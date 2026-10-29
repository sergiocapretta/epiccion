<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>E-Piccion — Messaggi OpenPGP</title>
<link rel="stylesheet" href="style.css">
<style>
.site-logo{display:block;width:100%;height:100%;max-width:96px;max-height:96px;object-fit:contain;margin:0 auto}
.panel{margin:22px 0;padding:22px;border:1px solid #d8e0e8;border-radius:10px;background:#fff}
.panel h2{color:#1f3c63;margin-bottom:10px}
.panel p{line-height:1.5;color:#555;margin:8px 0}
.panel label{margin-top:16px}
.panel .ep-textarea{font-family:ui-monospace,monospace;font-size:13px;min-height:150px}
.panel .ep-input,.panel select{width:100%;padding:12px;border:1px solid #cfd8e3;border-radius:8px;font-size:16px;margin-top:6px}
.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
.actions button{background:#285f99;color:#fff}
.actions button.primary{background:#2d7d46}
.notice{padding:12px 15px;border-radius:7px;margin:14px 0;line-height:1.45;background:#edf5ff;border-left:4px solid #3978b8}
.notice.error{background:#ffe9e9;border-color:#c62828;color:#8a0000}
.notice.info{background:#edf5ff;border-left:4px solid #3978b8}
.notice.update-available{background:#fff4dc;border-left:4px solid #c88719;color:#694400}
#openpgpUpdateStatus p{margin-top:10px}
.small{font-size:13px;color:#666}
a{color:#285f99}
</style>
</head>
<body>
<div class="container">
<header>
<div class="logo"><img src="../logo.png" alt="E-Piccion" class="site-logo"></div>
<h1>Messaggi OpenPGP</h1>
<p class="subtitle">Cifra con la chiave pubblica, decifra con la chiave privata</p>
<p class="engine"><a href="keys.php">← Gestione delle chiavi</a> · <a href="index.php">Modalità password v0.3</a></p>
</header>
<div id="status" class="notice" role="status" aria-live="polite">Caricamento di OpenPGP.js e dell'alfabeto emoji…</div>
<div id="openpgpUpdateStatus" class="notice info" role="status" aria-live="polite">Controllo aggiornamenti di OpenPGP.js…</div>


<section class="panel">
<h2>1. Cifra un messaggio</h2>
<p>Seleziona un contatto pubblico importato nella pagina di gestione chiavi. Il testo viene cifrato nel browser e poi rappresentato con l'alfabeto di 1.024 emoji.</p>
<label for="recipient">Destinatario</label>
<select id="recipient"><option value="">Caricamento contatti…</option></select>
<label for="plainMessage">Messaggio da cifrare</label>
<textarea class="ep-textarea" id="plainMessage" rows="7" placeholder="Scrivi qui il messaggio…"></textarea>
<div class="actions"><button id="encryptMessage" class="primary" type="button" disabled>🔒 Cifra e converti in emoji</button></div>
<label for="cipherOutput">Messaggio cifrato da inviare</label>
<textarea class="ep-textarea" id="cipherOutput" rows="8" readonly spellcheck="false"></textarea>
<div class="actions"><button data-copy="cipherOutput" type="button">Copia messaggio emoji</button><button data-download="cipherOutput" data-filename="epiccion-messaggio-emoji.txt" type="button">Scarica .txt</button></div>
</section>

<section class="panel">
<h2>2. Decifra un messaggio</h2>
<p>Incolla il testo emoji ricevuto e la tua chiave privata OpenPGP. La passphrase viene usata solo in questa operazione e non viene memorizzata.</p>
<label for="privateKeyInput">La tua chiave privata OpenPGP (ASCII-armored)</label>
<p class="small">Puoi incollare la chiave qui sotto oppure importarla da un file locale. Il file viene letto nel browser e non viene caricato sul server.</p>
<div class="actions"><button id="importPrivateKey" type="button">📂 Importa chiave privata da file</button></div>
<input id="privateKeyFile" type="file" accept=".asc,.pgp,.gpg,.txt,application/pgp-keys,text/plain" hidden>
<textarea class="ep-textarea" id="privateKeyInput" spellcheck="false" autocomplete="off" placeholder="-----BEGIN PGP PRIVATE KEY BLOCK-----"></textarea>
<label for="privatePassphrase">Passphrase della chiave privata</label>
<input class="ep-input" id="privatePassphrase" type="password" autocomplete="current-password">
<label for="cipherInput">Messaggio emoji ricevuto</label>
<textarea class="ep-textarea" id="cipherInput" rows="8" spellcheck="false" placeholder="Incolla qui il messaggio emoji completo…"></textarea>
<div class="actions"><button id="decryptMessage" type="button" disabled>🔓 Decifra messaggio</button><button id="clearSensitive" type="button">Svuota campi sensibili</button></div>
<label for="plainOutput">Testo decifrato</label>
<textarea class="ep-textarea" id="plainOutput" rows="7" readonly></textarea>
</section>
<footer>E-Piccion v0.4 — OpenPGP sperimentale</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/openpgp@6.3.2/dist/openpgp.min.js" integrity="sha384-Q6tBjZrAPJTIxj5F9KxCni/T9dTGDsXfi4U7aBZOl+3zPHpzU6gKcyKzAhs8Xeoa" crossorigin="anonymous" defer></script>
<script src="openpgp-update-check.js" defer></script>
<script src="openpgp-message.js" defer></script>
</body>
</html>
