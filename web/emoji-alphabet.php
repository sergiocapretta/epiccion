<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$alphabet = require __DIR__ . '/../engine/EmojiAlphabet.php';
if (!is_array($alphabet) || count($alphabet) !== 1024 || count(array_unique($alphabet)) !== 1024) {
    http_response_code(500);
    echo json_encode(['error' => 'Alfabeto emoji non valido.']);
    exit;
}
echo json_encode(array_values($alphabet), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
