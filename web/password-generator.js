"use strict";

document.addEventListener("DOMContentLoaded", function () {
    const lengthInput = document.getElementById("passwordLength");
    const lengthValue = document.getElementById("passwordLengthValue");
    const output = document.getElementById("generatedPassword");
    const status = document.getElementById("generatorStatus");
    const generateButton = document.getElementById("generatePassword");
    const copyButton = document.getElementById("copyPassword");

    const groups = [
        { id: "useUppercase", chars: "ABCDEFGHIJKLMNOPQRSTUVWXYZ", ambiguous: "IO" },
        { id: "useLowercase", chars: "abcdefghijklmnopqrstuvwxyz", ambiguous: "l" },
        { id: "useNumbers", chars: "0123456789", ambiguous: "01" },
        { id: "useSymbols", chars: "!@#$%^&*()-_=+[]{};:,.?", ambiguous: "" }
    ];

    lengthInput.addEventListener("input", function () {
        lengthValue.value = lengthInput.value;
        lengthValue.textContent = lengthInput.value;
    });

    // Estrazione uniforme: evita il bias del semplice operatore modulo.
    function secureRandomInt(max) {
        if (!Number.isInteger(max) || max < 1 || max > 0x100000000) {
            throw new Error("Intervallo casuale non valido.");
        }
        const range = 0x100000000;
        const limit = Math.floor(range / max) * max;
        const value = new Uint32Array(1);
        do {
            window.crypto.getRandomValues(value);
        } while (value[0] >= limit);
        return value[0] % max;
    }

    function shuffleSecurely(items) {
        for (let i = items.length - 1; i > 0; i--) {
            const j = secureRandomInt(i + 1);
            [items[i], items[j]] = [items[j], items[i]];
        }
        return items;
    }

    function generate() {
        if (!window.crypto || typeof window.crypto.getRandomValues !== "function") {
            throw new Error("Questo browser non supporta la generazione casuale sicura. Usa un browser aggiornato tramite HTTPS.");
        }

        const excludeAmbiguous = document.getElementById("excludeAmbiguous").checked;
        const selected = groups
            .filter(group => document.getElementById(group.id).checked)
            .map(group => ({
                chars: excludeAmbiguous
                    ? Array.from(group.chars).filter(char => !group.ambiguous.includes(char)).join("")
                    : group.chars
            }))
            .filter(group => group.chars.length > 0);

        if (selected.length === 0) {
            throw new Error("Seleziona almeno un tipo di carattere.");
        }

        const length = Number(lengthInput.value);
        if (!Number.isInteger(length) || length < selected.length || length > 128) {
            throw new Error("La lunghezza scelta non è valida per le opzioni selezionate.");
        }

        const allChars = selected.map(group => group.chars).join("");
        const passwordChars = selected.map(group => group.chars[secureRandomInt(group.chars.length)]);

        while (passwordChars.length < length) {
            passwordChars.push(allChars[secureRandomInt(allChars.length)]);
        }

        output.value = shuffleSecurely(passwordChars).join("");
        status.textContent = "Password generata. Puoi copiarla e conservarla in un luogo sicuro.";
    }

    generateButton.addEventListener("click", function () {
        try {
            generate();
        } catch (error) {
            output.value = "";
            status.textContent = error.message || "Impossibile generare la password.";
        }
    });

    copyButton.addEventListener("click", async function () {
        if (!output.value) {
            status.textContent = "Genera prima una password.";
            return;
        }
        try {
            await navigator.clipboard.writeText(output.value);
            status.textContent = "Password copiata negli appunti.";
        } catch (error) {
            output.focus();
            output.select();
            const copied = document.execCommand("copy");
            status.textContent = copied
                ? "Password copiata negli appunti."
                : "Copia non riuscita: seleziona e copia manualmente la password.";
        }
    });
});
