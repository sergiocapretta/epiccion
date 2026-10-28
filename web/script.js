/*
|--------------------------------------------------------------------------
|
| E-Piccion
|
| "Affida il tuo messaggio a un piccione."
|
| Powered by EmojiCrypt Engine
|
|--------------------------------------------------------------------------
*/

"use strict";

/**
 * Copia il contenuto del campo risultato negli appunti.
 */
async function copyResult() {

    const textarea = document.getElementById("result");

    if (!textarea) {
        return;
    }

    try {

        await navigator.clipboard.writeText(textarea.value);

        showMessage("📋 Testo copiato negli appunti.");

    } catch (err) {

        textarea.select();
        textarea.setSelectionRange(0, 999999);

        document.execCommand("copy");

        showMessage("📋 Testo copiato.");

    }

}

/**
 * Mostra un piccolo messaggio temporaneo.
 */
function showMessage(text) {

    let box = document.getElementById("epiccion-message");

    if (!box) {

        box = document.createElement("div");

        box.id = "epiccion-message";

        box.style.position = "fixed";
        box.style.bottom = "20px";
        box.style.right = "20px";

        box.style.background = "#2d7d46";
        box.style.color = "#ffffff";

        box.style.padding = "12px 18px";

        box.style.borderRadius = "8px";

        box.style.boxShadow =
            "0 5px 15px rgba(0,0,0,.25)";

        box.style.zIndex = 9999;

        box.style.fontSize = "14px";

        document.body.appendChild(box);

    }

    box.textContent = text;

    box.style.opacity = "1";

    clearTimeout(box.timer);

    box.timer = setTimeout(function () {

        box.style.opacity = "0";

    }, 2500);

}

/**
 * Se l'utente preme ESC,
 * deseleziona eventuali campi.
 */
document.addEventListener("keydown", function (event) {

    if (event.key === "Escape") {

        document.activeElement.blur();

    }

});

/**
 * Se esiste il risultato,
 * selezionalo automaticamente
 * al doppio click.
 */
document.addEventListener("DOMContentLoaded", function () {

    const result = document.getElementById("result");

    if (!result) {
        return;
    }

    result.addEventListener("dblclick", function () {

        result.select();

    });

});

/*
|--------------------------------------------------------------------------
| Mostra / Nasconde la password
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {

    const password = document.getElementById("password");
    const button = document.getElementById("togglePassword");

    if (!password || !button) {
        return;
    }

    button.addEventListener("click", function () {

        if (password.type === "password") {

            password.type = "text";
            button.textContent = "🙈 Nascondi password";

        } else {

            password.type = "password";
            button.textContent = "👁️ Mostra password";

        }

    });

});

document.addEventListener("DOMContentLoaded", function(){

    const btn=document.getElementById("installButton");

    if(!btn) return;

    btn.addEventListener("click", installAlphabet);

});

function installAlphabet() {

    const box = document.querySelector(".install-box");

    box.innerHTML = `
        <h2>🕊️ Preparazione di E-Piccion...</h2>

        <div class="progress">
            <div class="bar" id="bar"></div>
        </div>

        <p id="status">
            Preparazione...
        </p>
    `;

    const bar = document.getElementById("bar");
    const status = document.getElementById("status");

    // Le varie fasi dell'installazione
    const steps = [

        { progress: 15, text: "🕊️ Sto scegliendo i piccioni migliori..." },

        { progress: 30, text: "🪶 Sto preparando le bisacce..." },

        { progress: 45, text: "🗺️ Sto studiando la rotta..." },

        { progress: 60, text: "😊 Sto insegnando le emoji ai piccioni..." },

        { progress: 75, text: "📬 Ogni messaggio avrà il suo messaggero..." },

        { progress: 90, text: "🌍 Lo stormo è quasi pronto al decollo..." },

        { progress: 100, text: "✨ Ultimi controlli..." }

    ];

    let current = 0;
    let installFinished = false;

    // Parte SUBITO il generatore
    fetch("../tools/generateAlphabet.php")
        .then(r => r.text())
        .then(() => {

            installFinished = true;

        });

    // Animazione (circa 10 secondi)
    const timer = setInterval(function () {

        if (current < steps.length) {

            bar.style.width = steps[current].progress + "%";
            status.innerHTML = steps[current].text;

            current++;

            return;
        }

        // Finita l'animazione
        clearInterval(timer);

        // Se il generatore non ha ancora finito aspettiamo
        const wait = setInterval(function () {

            if (!installFinished)
                return;

            clearInterval(wait);

            status.innerHTML =
                "✅ <strong>I piccioni sono pronti!</strong><br><br>" +
                "Benvenuto in E-Piccion.";

            setTimeout(function () {

                window.location = "index.php";

            }, 2000);

        }, 100);

    }, 1400);

}

