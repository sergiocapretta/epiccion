<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Piccion — Chiavi OpenPGP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .key-panel{margin:24px 0;padding:22px;border:1px solid #d8e0e8;border-radius:10px;background:#fff}
        .key-panel h2{color:#1f3c63;margin-bottom:10px}
        .key-panel p{line-height:1.5;color:#555;margin:8px 0}
        .key-panel label{margin-top:16px}
        .key-panel .ep-input,.key-panel .ep-textarea{margin-top:6px}
        .key-panel .ep-textarea{font-family:ui-monospace,monospace;font-size:13px;min-height:130px}
        .key-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
        .key-actions button{background:#285f99;color:#fff}
        .key-actions button.primary{background:#2d7d46}
        .key-actions button.secondary{background:#edf1f5;color:#24364b}
        .notice{padding:12px 15px;border-radius:7px;margin:14px 0;line-height:1.45}
        .notice.info{background:#edf5ff;border-left:4px solid #3978b8}
        .notice.error{background:#ffe9e9;border-left:4px solid #c62828;color:#8a0000}
        .contact-item{padding:12px;border:1px solid #d8e0e8;border-radius:7px;margin-top:10px;overflow-wrap:anywhere}
        .contact-item button{margin-top:8px;background:#edf1f5;color:#24364b}
        .small{font-size:13px;color:#666}
        a{color:#285f99}
    </style>
</head>
<body>
<div class="container">
    <header>
        <div class="logo">🗝️</div>
        <h1>Chiavi OpenPGP</h1>
        <p class="subtitle">Gestione locale delle chiavi per E-Piccion</p>
        <p class="engine"><a href="openpgp-message.php">Vai ai messaggi OpenPGP →</a> · <a href="index.php">Torna alla modalità password</a></p>
    </header>

    <div class="notice info">
        <strong>Prototipo in sviluppo.</strong> Le operazioni di questa pagina vengono eseguite nel browser. La chiave privata non viene inviata a E-Piccion; per questa prima prova non viene salvata automaticamente. Esporta e conserva la chiave privata in un luogo sicuro.
    </div>
    <div id="status" class="notice info" role="status" aria-live="polite">Caricamento della libreria OpenPGP…</div>

    <section class="key-panel">
        <h2>1. Genera la tua coppia di chiavi</h2>
        <p>La chiave pubblica si può condividere. La chiave privata deve restare sotto il tuo controllo ed è protetta dalla passphrase scelta qui.</p>
        <label for="userName">Nome (facoltativo)</label>
        <input class="ep-input" id="userName" autocomplete="name" maxlength="100" placeholder="Nome o pseudonimo">
        <label for="userEmail">Email (facoltativa)</label>
        <input class="ep-input" id="userEmail" type="email" autocomplete="email" maxlength="200" placeholder="nome@example.org">
        <label for="newPassphrase">Passphrase della chiave privata</label>
        <input class="ep-input" id="newPassphrase" type="password" autocomplete="new-password" minlength="10" placeholder="Almeno 10 caratteri">
        <label for="newPassphraseConfirm">Ripeti la passphrase</label>
        <input class="ep-input" id="newPassphraseConfirm" type="password" autocomplete="new-password" minlength="10">
        <div class="key-actions"><button class="primary" id="generateKey" type="button" disabled>Genera coppia di chiavi</button></div>
    </section>

    <section class="key-panel">
        <h2>2. Importa una chiave privata esistente</h2>
        <p>Puoi incollare una chiave privata OpenPGP ASCII-armored esportata da un altro programma. La passphrase non viene memorizzata.</p>
        <label for="privateKeyInput">Chiave privata OpenPGP</label>
        <textarea class="ep-textarea" id="privateKeyInput" spellcheck="false" autocomplete="off" placeholder="-----BEGIN PGP PRIVATE KEY BLOCK-----"></textarea>
        <div class="key-actions"><button id="importPrivateKey" type="button" disabled>Verifica / importa chiave privata</button></div>
    </section>

    <section class="key-panel">
        <h2>3. Importa la chiave pubblica di un contatto</h2>
        <p>La chiave pubblica serve per cifrare un messaggio destinato a quella persona. La rubrica viene conservata solo nel localStorage di questo browser.</p>
        <label for="contactName">Nome del contatto</label>
        <input class="ep-input" id="contactName" maxlength="100" placeholder="Nome o soprannome">
        <label for="publicKeyInput">Chiave pubblica OpenPGP</label>
        <textarea class="ep-textarea" id="publicKeyInput" spellcheck="false" autocomplete="off" placeholder="-----BEGIN PGP PUBLIC KEY BLOCK-----"></textarea>
        <div class="key-actions"><button id="importPublicKey" type="button" disabled>Verifica e aggiungi contatto</button></div>
        <div id="contactsList" aria-live="polite"></div>
    </section>

    <section class="key-panel">
        <h2>4. Chiave della sessione</h2>
        <p class="small">Questi campi restano nella pagina corrente. Copia ed esporta le chiavi che vuoi conservare; ricaricando la pagina, i campi vengono svuotati.</p>
        <label for="publicKeyOutput">La tua chiave pubblica — da condividere</label>
        <textarea class="ep-textarea" id="publicKeyOutput" readonly spellcheck="false"></textarea>
        <div class="key-actions"><button class="secondary" data-copy="publicKeyOutput" type="button">Copia chiave pubblica</button><button class="secondary" data-download="publicKeyOutput" data-filename="epiccion-chiave-pubblica.asc" type="button">Scarica .asc</button></div>
        <label for="privateKeyOutput">La tua chiave privata — NON condividere</label>
        <textarea class="ep-textarea" id="privateKeyOutput" readonly spellcheck="false"></textarea>
        <div class="key-actions"><button class="secondary" data-copy="privateKeyOutput" type="button">Copia chiave privata</button><button class="secondary" data-download="privateKeyOutput" data-filename="epiccion-chiave-privata.asc" type="button">Scarica .asc</button><button class="secondary" id="clearSession" type="button">Svuota campi sensibili</button></div>
        <p class="small">Conserva una copia di sicurezza della chiave privata e della passphrase separatamente. Se perdi una delle due, potresti non riuscire più a leggere i messaggi destinati a questa chiave.</p>
    </section>

    <footer>E-Piccion v0.4 — OpenPGP in fase sperimentale</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/openpgp@6.3.2/dist/openpgp.min.js" defer></script>
<script src="keys.js" defer></script>
</body>
</html>
