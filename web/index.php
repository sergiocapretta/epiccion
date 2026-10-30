<?php
declare(strict_types=1);

$alphabetFile = __DIR__ . "/../engine/EmojiAlphabet.php";

if (!file_exists($alphabetFile)) {

    header("Location: install.php");
    exit;

}
require_once __DIR__ . '/../engine/EmojiCrypt.php';

$alphabet = file_exists(__DIR__ . '/../engine/EmojiAlphabet.php')
    ? require __DIR__ . '/../engine/EmojiAlphabet.php'
    : [];

$result = '';
$message = '';
$password = '';
$mode = 'encrypt';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $message = trim($_POST['message'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $mode = $_POST['mode'] ?? 'encrypt';

    try {

        if ($password === '') {
            throw new Exception('Inserisci una password.');
        }

        if (empty($alphabet)) {
            throw new Exception(
                'EmojiAlphabet.php non trovato. Genera prima l\'alfabeto.'
            );
        }

        $crypt = new EmojiCrypt($password, $alphabet);

        if ($mode === 'encrypt') {
            $result = $crypt->encrypt($message);
        } else {
            $result = $crypt->decrypt($message);
        }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>E-Piccion</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .ep-navigation{margin:22px 0 28px;padding:20px;border:1px solid #d8e0e8;border-radius:12px;background:#f8fafc}
        .ep-navigation h2{margin:0 0 8px;color:#1f3c63;font-size:1.25rem}
        .ep-navigation p{line-height:1.5;color:#526174;margin:8px 0 14px}
        .ep-navigation-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
        .ep-navigation-card{display:flex;flex-direction:column;gap:8px;padding:16px;border:1px solid #d4deea;border-radius:9px;background:#fff;color:#234a73;text-decoration:none;transition:transform .15s ease,box-shadow .15s ease}
        .ep-navigation-card:hover,.ep-navigation-card:focus-visible{transform:translateY(-2px);box-shadow:0 4px 14px rgba(25,55,90,.12);outline:2px solid #7da8d8;outline-offset:2px}
        .ep-navigation-card strong{font-size:1rem}
        .ep-navigation-card span:last-child{font-size:.9rem;line-height:1.4;color:#596879}
        .ep-navigation-icon{font-size:1.7rem}
        .ep-navigation-primary{background:#eaf3ff;border-color:#9dbde1}
        .ep-navigation-note{font-size:.85rem!important;margin:14px 0 0!important}
        .ep-password-intro{margin:28px 0 16px;padding:18px 20px;border-left:4px solid #9dbde1;background:#f8fafc;border-radius:8px}
        .ep-password-intro h2{margin:0 0 8px;color:#1f3c63;font-size:1.25rem}
        .ep-password-intro p{margin:0;color:#526174;line-height:1.5}
        @media(max-width:480px){.ep-navigation{padding:14px}.ep-navigation-grid{grid-template-columns:1fr}}
    </style>

</head>
<body>

<div class="container">

    <header>

        <div class="logo">
<img src="../logo.png" alt="E-Piccion">
        </div>

        <h1>E-Piccion</h1>

        <p class="subtitle">
            Affida il tuo messaggio a un piccione.
        </p>

        <p class="engine">
            Powered by Sergio Capretta
        </p>

    </header>


    <nav class="ep-navigation" aria-label="Sezioni E-Piccion">
        <h2>Modalità OpenPGP</h2>
        <p>Per inviare messaggi con chiavi personali, configura prima la tua coppia di chiavi e aggiungi i contatti alla rubrica.</p>
        <div class="ep-navigation-grid">
            <a class="ep-navigation-card" href="keys.php">
                <span class="ep-navigation-icon" aria-hidden="true">🗝️</span>
                <strong>Gestione chiavi</strong>
                <span>Genera una coppia di chiavi o importa una chiave esistente.</span>
            </a>
            <a class="ep-navigation-card" href="keys.php#rubrica">
                <span class="ep-navigation-icon" aria-hidden="true">👥</span>
                <strong>Rubrica contatti</strong>
                <span>Importa e gestisci le chiavi pubbliche delle persone con cui comunicare.</span>
            </a>
            <a class="ep-navigation-card ep-navigation-primary" href="openpgp-message.php">
                <span class="ep-navigation-icon" aria-hidden="true">✉️</span>
                <strong>Scrivi un messaggio cifrato</strong>
                <span>Scegli un destinatario dalla rubrica e prepara il messaggio in emoji.</span>
            </a>
        </div>
    </nav>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <section class="ep-password-intro" aria-labelledby="password-mode-title">
        <h2 id="password-mode-title">Modalità password semplice</h2>
        <p>La modalità password semplice qui sotto utilizza il motore v0.3 di E-Piccion. I messaggi OpenPGP usano un formato distinto.</p>
    </section>

    <form method="post">

<div class="password-box">

<label for="password">Password</label>

<input
    class="ep-input"
    type="password"
    id="password"
    name="password"
    value="<?= htmlspecialchars($password) ?>"
    required
>

<button
    type="button"
    id="togglePassword"
    class="toggle-password"
>
    👁️ Mostra password
</button>

</div>

        <label>Messaggio</label>

<textarea
    class="ep-textarea"
    name="message"
    rows="8"
    required
><?= htmlspecialchars($message) ?></textarea>

        <div class="buttons">

            <button
                type="submit"
                name="mode"
                value="encrypt"
            >
                🔒 Cripta
            </button>

            <button
                type="submit"
                name="mode"
                value="decrypt"
            >
                🔓 Decripta
            </button>

        </div>

    </form>

    <?php if ($result !== ''): ?>

        <div class="result">

            <h2>Risultato</h2>

<textarea
    class="ep-textarea"
    id="result"
    rows="8"
    readonly
><?= htmlspecialchars($result) ?></textarea>

            <button
                type="button"
                onclick="copyResult()"
            >
                📋 Copia
            </button>

        </div>

    <?php endif; ?>

    <footer>

        E-Piccion v0.3

    </footer>

</div>

<script src="script.js"></script>

</body>
</html>
