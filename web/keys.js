"use strict";

(() => {
    const $ = (id) => document.getElementById(id);
    let openpgpReady = false;
    const CONTACTS_KEY = "epiccion.openpgp.contacts.v1";

    function status(message, kind = "info") {
        const box = $("status");
        box.className = "notice " + kind;
        box.textContent = message;
    }

    function setButtonsEnabled(enabled) {
        ["generateKey", "importPrivateKey", "importPublicKey"].forEach(id => {
            $(id).disabled = !enabled;
        });
    }

    function safeContacts() {
        try {
            const value = JSON.parse(localStorage.getItem(CONTACTS_KEY) || "[]");
            return Array.isArray(value) ? value.filter(x =>
                x && typeof x.name === "string" &&
                typeof x.fingerprint === "string" &&
                typeof x.publicKey === "string"
            ) : [];
        } catch (_) {
            return [];
        }
    }

    function renderContacts() {
        const root = $("contactsList");
        root.replaceChildren();
        const contacts = safeContacts();
        if (!contacts.length) {
            const p = document.createElement("p");
            p.className = "small";
            p.textContent = "Nessun contatto importato.";
            root.appendChild(p);
            return;
        }
        contacts.forEach(contact => {
            const item = document.createElement("div");
            item.className = "contact-item";
            const title = document.createElement("strong");
            title.textContent = contact.name || "Contatto senza nome";
            const fingerprint = document.createElement("p");
            fingerprint.className = "small";
            fingerprint.textContent = "Fingerprint: " + contact.fingerprint;
            const remove = document.createElement("button");
            remove.type = "button";
            remove.textContent = "Rimuovi contatto";
            remove.addEventListener("click", () => {
                const updated = safeContacts().filter(x => x.fingerprint !== contact.fingerprint);
                localStorage.setItem(CONTACTS_KEY, JSON.stringify(updated));
                renderContacts();
                status("Contatto rimosso da questo browser.");
            });
            item.append(title, fingerprint, remove);
            root.appendChild(item);
        });
    }

    async function generateKeyPair() {
        const name = $("userName").value.trim();
        const email = $("userEmail").value.trim();
        const passphrase = $("newPassphrase").value;
        const confirm = $("newPassphraseConfirm").value;
        if (!name && !email) throw new Error("Inserisci almeno un nome o un indirizzo email.");
        if (passphrase.length < 10) throw new Error("Scegli una passphrase di almeno 10 caratteri.");
        if (passphrase !== confirm) throw new Error("Le passphrase non coincidono.");

        const userID = {};
        if (name) userID.name = name;
        if (email) userID.email = email;
        status("Generazione della coppia di chiavi in corso nel browser…");
        const result = await openpgp.generateKey({
            type: "ecc",
            curve: "curve25519",
            userIDs: [userID],
            passphrase,
            format: "armored"
        });
        $("publicKeyOutput").value = result.publicKey;
        $("privateKeyOutput").value = result.privateKey;

        const sessionPanel = $("sessionKeyPanel");
        if (sessionPanel) {
            sessionPanel.open = true;
            sessionPanel.scrollIntoView({ behavior: "smooth", block: "start" });
            const summary = sessionPanel.querySelector("summary");
            if (summary) summary.focus({ preventScroll: true });
        }

        $("newPassphrase").value = "";
        $("newPassphraseConfirm").value = "";
        status("Coppia di chiavi generata. Esporta subito la chiave privata e conserva la passphrase: non viene salvata da E-Piccion.");
    }

    async function importPrivateKey() {
        const armored = $("privateKeyInput").value.trim();
        if (!armored) throw new Error("Incolla prima una chiave privata.");
        const key = await openpgp.readPrivateKey({ armoredKey: armored });
        const fingerprint = key.getFingerprint().toUpperCase().match(/.{1,4}/g).join(" ");
        $("privateKeyOutput").value = armored;
        const publicKey = await openpgp.readKey({ armoredKey: await key.toPublic().armor() });
        $("publicKeyOutput").value = publicKey.armor();
        status("Chiave privata OpenPGP valida importata nella pagina. Fingerprint: " + fingerprint + ". La passphrase sarà richiesta quando servirà decifrare o firmare.");
    }

    async function importPublicKey() {
        const armored = $("publicKeyInput").value.trim();
        if (!armored) throw new Error("Incolla prima una chiave pubblica.");
        const key = await openpgp.readKey({ armoredKey: armored });
        const fingerprint = key.getFingerprint().toUpperCase();
        const enteredName = $("contactName").value.trim();
        const user = key.getUserIDs()[0] || "";
        const name = enteredName || user || fingerprint.slice(0, 16);
        const contacts = safeContacts().filter(x => x.fingerprint !== fingerprint);
        contacts.push({ name, fingerprint, publicKey: key.armor() });
        localStorage.setItem(CONTACTS_KEY, JSON.stringify(contacts));
        $("contactName").value = "";
        $("publicKeyInput").value = "";
        renderContacts();
        status("Chiave pubblica verificata sintatticamente e aggiunta alla rubrica locale. Prima di usarla, verifica il fingerprint con il proprietario tramite un canale indipendente.");
    }

    function downloadText(text, filename) {
        const blob = new Blob([text], { type: "application/pgp-keys; charset=utf-8" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    }

    document.addEventListener("DOMContentLoaded", () => {
        renderContacts();
        const waitForLibrary = () => {
            if (window.openpgp && typeof window.openpgp.generateKey === "function") {
                openpgpReady = true;
                setButtonsEnabled(true);
                status("Libreria OpenPGP.js caricata. La gestione delle chiavi è pronta.");
                return;
            }
            if (document.readyState === "complete") {
                status("Impossibile caricare OpenPGP.js. Controlla la connessione e ricarica la pagina.", "error");
                return;
            }
            window.setTimeout(waitForLibrary, 100);
        };
        waitForLibrary();

        $("choosePrivateKeyFile").addEventListener("click", () => $("privateKeyFile").click());
        $("choosePublicKeyFile").addEventListener("click", () => $("publicKeyFile").click());

        async function readKeyFile(file, targetId, expectedLabel) {
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) {
                status("Il file supera il limite di 2 MB.", "error");
                return;
            }
            try {
                const content = await file.text();
                if (!content.trim()) throw new Error("Il file selezionato è vuoto.");
                if (!content.includes("-----BEGIN PGP ")) {
                    throw new Error("Il file non sembra contenere una chiave OpenPGP ASCII-armored (.asc).");
                }
                $(targetId).value = content.trim();
                status("File " + expectedLabel + " caricato. Premi il pulsante di verifica/importazione per completare l'operazione.");
            } catch (error) {
                status(error.message || "Impossibile leggere il file selezionato.", "error");
            }
        }

        $("privateKeyFile").addEventListener("change", event => {
            readKeyFile(event.target.files && event.target.files[0], "privateKeyInput", "della chiave privata");
            event.target.value = "";
        });
        $("publicKeyFile").addEventListener("change", event => {
            readKeyFile(event.target.files && event.target.files[0], "publicKeyInput", "della chiave pubblica");
            event.target.value = "";
        });

        $("generateKey").addEventListener("click", async () => {
            if (!openpgpReady) return;
            try { await generateKeyPair(); }
            catch (error) { status(error.message || "Errore durante la generazione della chiave.", "error"); }
        });
        $("importPrivateKey").addEventListener("click", async () => {
            if (!openpgpReady) return;
            try { await importPrivateKey(); }
            catch (error) { status(error.message || "Chiave privata non valida.", "error"); }
        });
        $("importPublicKey").addEventListener("click", async () => {
            if (!openpgpReady) return;
            try { await importPublicKey(); }
            catch (error) { status(error.message || "Chiave pubblica non valida.", "error"); }
        });
        document.querySelectorAll("[data-copy]").forEach(button => button.addEventListener("click", async () => {
            const value = $(button.dataset.copy).value;
            if (!value) { status("Non c'è alcuna chiave da copiare.", "error"); return; }
            try {
                await navigator.clipboard.writeText(value);
                status("Chiave copiata negli appunti.");
            } catch (_) {
                status("Il browser non ha consentito l'accesso agli appunti: seleziona e copia il testo manualmente.", "error");
            }
        }));
        document.querySelectorAll("[data-download]").forEach(button => button.addEventListener("click", () => {
            const value = $(button.dataset.download).value;
            if (!value) { status("Non c'è alcuna chiave da scaricare.", "error"); return; }
            downloadText(value, button.dataset.filename);
            status("Download avviato. Conserva la chiave privata in un luogo sicuro.");
        }));
        $("clearSession").addEventListener("click", () => {
            $("privateKeyInput").value = "";
            $("publicKeyInput").value = "";
            $("publicKeyOutput").value = "";
            $("privateKeyOutput").value = "";
            $("newPassphrase").value = "";
            $("newPassphraseConfirm").value = "";
            status("Campi contenenti chiavi e passphrase svuotati. I contatti pubblici restano nella rubrica locale.");
        });
    });
})();
