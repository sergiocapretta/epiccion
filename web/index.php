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

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

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

        E-Piccion v0.2a

    </footer>

</div>

<script src="script.js"></script>

</body>
</html>
