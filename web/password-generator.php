<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E-Piccion — Generatore password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .generator-panel{margin:16px 0;padding:22px;border:1px solid #d8e0e8;border-radius:10px;background:#fff}
        .generator-panel p{color:#526174;line-height:1.5;margin:8px 0 16px}
        .generator-panel label{margin-top:16px}
        .length-row{display:flex;align-items:center;gap:14px}
        .length-row input[type=range]{flex:1;min-width:0;accent-color:#285f99}
        .length-row output{min-width:3.2em;text-align:center;font-weight:700;color:#1f3c63}
        .option-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-top:12px}
        .option{display:flex;align-items:center;gap:9px;margin:0;padding:10px 12px;border:1px solid #e0e6ed;border-radius:7px;font-weight:500}
        .option input{accent-color:#285f99;width:17px;height:17px}
        .generator-output{display:flex;gap:10px;align-items:stretch;margin-top:22px}
        .generator-output input{font-family:ui-monospace,monospace;font-size:17px}
        .generator-output button,.generator-actions button{background:#285f99;color:#fff}
        .generator-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
        .generator-note{font-size:13px;color:#666!important;margin-top:18px!important}
        #generatorStatus{min-height:1.4em;margin-top:12px;color:#285f99}
        @media(max-width:600px){.generator-panel{padding:16px}.generator-output{flex-direction:column}.generator-actions button{width:100%}}
    </style>
</head>
<body>
<div class="container">
    <header>
        <div class="logo">🔐</div>
        <h1>Generatore password</h1>
        <p class="subtitle">Crea password casuali per i tuoi servizi e le tue applicazioni</p>
        <p class="engine"><a href="index.php">← Torna a E-Piccion</a></p>
    </header>

    <section class="generator-panel">
        <p>Personalizza la password scegliendo la lunghezza e i tipi di caratteri da utilizzare.</p>
        <label for="passwordLength">Lunghezza della password</label>
        <div class="length-row">
            <input id="passwordLength" type="range" min="8" max="128" value="24">
            <output id="passwordLengthValue" for="passwordLength">24</output>
        </div>

        <label>Caratteri da includere</label>
        <div class="option-grid">
            <label class="option"><input type="checkbox" id="useUppercase" checked> Lettere maiuscole</label>
            <label class="option"><input type="checkbox" id="useLowercase" checked> Lettere minuscole</label>
            <label class="option"><input type="checkbox" id="useNumbers" checked> Numeri</label>
            <label class="option"><input type="checkbox" id="useSymbols" checked> Simboli</label>
        </div>
        <div class="option-grid">
            <label class="option"><input type="checkbox" id="excludeAmbiguous" checked> Escludi caratteri ambigui (I, l, 1, O, 0)</label>
        </div>

        <div class="generator-actions">
            <button type="button" id="generatePassword">Genera password</button>
        </div>

        <label for="generatedPassword">Password generata</label>
        <div class="generator-output">
            <input class="ep-input" id="generatedPassword" type="text" readonly autocomplete="off" autocapitalize="off" spellcheck="false" aria-label="Password generata">
            <button type="button" id="copyPassword">📋 Copia</button>
        </div>
        <p id="generatorStatus" role="status" aria-live="polite"></p>
        <p class="generator-note">La password viene generata interamente nel browser usando il generatore crittograficamente sicuro del dispositivo. Non viene inviata al server, salvata da E-Piccion o registrata. Se il browser non supporta la generazione sicura, lo strumento non produrrà password.</p>
    </section>
    <footer>E-Piccion v0.6 — Generatore password</footer>
</div>
<script src="password-generator.js" defer></script>
</body>
</html>
