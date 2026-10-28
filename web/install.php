<?php

declare(strict_types=1);

$alphabetFile = __DIR__ . "/../engine/EmojiAlphabet.php";

if (file_exists($alphabetFile)) {

    header("Location: index.php");
    exit;

}
?>

<!DOCTYPE html>

<html lang="it">

<head>

<meta charset="UTF-8">

<title>E-Piccion - Installazione</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

<div class="logo">

🕊️

</div>

<h1>E-Piccion</h1>

<p class="subtitle">

Affida il tuo messaggio a un piccione.

</p>

<div class="install-box">

<h2>

Prima inizializzazione

</h2>

<p><br>

È necessario preparare la piccionaia.

L'operazione richiede pochi secondi.<br>

</p>

<button id="installButton">

Genera piccioni

</button>

</div>

</div>

<script src="script.js"></script>

</body>

</html>
