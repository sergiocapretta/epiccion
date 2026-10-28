<?php
/**
 * E-Piccion - generateAlphabet.php
 *
 * Genera engine/EmojiAlphabet.php partendo dal file
 * resources/emoji-test.txt (formato Unicode Consortium).
 */

declare(strict_types=1);

const SOURCE =
    __DIR__ .
    '/../resources/emoji-test.txt';
const OUTPUT = __DIR__ . '/../engine/EmojiAlphabet.php';
const SEED   = 'E-Piccion v0.2a';
const MAX_EMOJI = 1024;

if (!file_exists(SOURCE)) {
    exit("Sorgente non trovata: ".SOURCE.PHP_EOL);
}

if (!file_exists(SOURCE)) {

    echo PHP_EOL;
    echo "File emoji-test.txt non trovato.".PHP_EOL;
    echo PHP_EOL;
    echo "Scaricalo da:".PHP_EOL;
    echo "https://www.unicode.org/Public/emoji/latest/emoji-test.txt".PHP_EOL;
    echo PHP_EOL;

    exit(1);
}

$map = [];

/*
 * Estrae solo le emoji "fully-qualified".
 * Formato tipico:
 * 1F600 ; fully-qualified # 😀 E1.0 grinning face
 */
foreach (file(SOURCE, FILE_IGNORE_NEW_LINES) as $line) {

    $line = trim($line);

    if ($line === '' || $line[0] === '#') {
        continue;
    }

    if (!str_contains($line, '; fully-qualified')) {
        continue;
    }

    if (!preg_match('/#\s+(\S+)/u', $line, $m)) {
        continue;
    }

    $emoji = $m[1];
    $map[$emoji] = true;       // elimina duplicati
}

$list = array_keys($map);

if (count($list) < MAX_EMOJI) {
    exit("Emoji trovate: ".count($list).". Ne servono almeno ".MAX_EMOJI.PHP_EOL);
}

/* Shuffle deterministico */
mt_srand(crc32(SEED));
for ($i = count($list)-1; $i > 0; $i--) {
    $j = mt_rand(0, $i);
    [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
}

$list = array_slice($list, 0, MAX_EMOJI);

$php = "<?php\n".
"/**\n".
" * FILE GENERATO AUTOMATICAMENTE\n".
" * E-Piccion - EmojiCrypt Engine\n".
" * NON MODIFICARE MANUALMENTE\n".
" */\n\nreturn ".
var_export($list, true).
";\n";

$outDir = dirname(OUTPUT);
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

file_put_contents(OUTPUT, $php);

echo "Creato ".OUTPUT." con ".count($list)." emoji.".PHP_EOL;
