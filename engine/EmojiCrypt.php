<?php
declare(strict_types=1);
/** E-Piccion - EmojiCrypt Engine v0.3 */
class EmojiCrypt {
    public const VERSION = '0.3';
    public const ALPHABET_SIZE = 1024;
    private const V2_PREFIX = 'EP1024:';

    private string $password;
    private array $alphabet;
    private array $emojiToIndex = [];

    public function __construct(string $password, array $alphabet) {
        if (count($alphabet) !== self::ALPHABET_SIZE) {
            throw new InvalidArgumentException("L'alfabeto Base1024 deve contenere esattamente 1024 emoji.");
        }
        $this->password = $password;
        $this->alphabet = array_values($alphabet);
        foreach ($this->alphabet as $i => $emoji) {
            if (isset($this->emojiToIndex[$emoji])) {
                throw new InvalidArgumentException('Emoji duplicata');
            }
            $this->emojiToIndex[$emoji] = $i;
        }
    }

    public function getVersion(): string { return self::VERSION; }
    public function getAlphabetSize(): int { return count($this->alphabet); }

    public function encrypt(string $text): string {
        $length = strlen($text);
        if ($length === 0) return self::V2_PREFIX . '0:';
        $symbolCount = (int) ceil(($length * 8) / 10);
        $out = [];
        for ($i = 0; $i < $symbolCount; $i++) {
            $value = 0;
            for ($j = 0; $j < 10; $j++) {
                $bitIndex = $i * 10 + $j;
                $value <<= 1;
                if ($bitIndex < $length * 8) {
                    $byte = ord($text[intdiv($bitIndex, 8)]);
                    $value |= ($byte >> (7 - ($bitIndex % 8))) & 1;
                }
            }
            $value ^= $this->keyWord($i);
            $out[] = $this->alphabet[$value];
        }
        return self::V2_PREFIX . $length . ':' . implode(' ', $out);
    }

    public function decrypt(string $cipher): string {
        $cipher = trim($cipher);
        if (preg_match('/^EP1024:(0|[1-9][0-9]*):(.*)$/us', $cipher, $matches) === 1) {
            $length = filter_var($matches[1], FILTER_VALIDATE_INT);
            if ($length === false || $length < 0 || $length > intdiv(PHP_INT_MAX, 8)) {
                throw new RuntimeException('Lunghezza del messaggio Base1024 non valida');
            }
            return $this->decrypt1024($length, trim($matches[2]));
        }
        return $this->decryptLegacy($cipher);
    }

    private function decrypt1024(int $byteLength, string $payload): string {
        if ($byteLength === 0) {
            if ($payload !== '') throw new RuntimeException('Messaggio Base1024 non valido');
            return '';
        }
        if ($payload === '') throw new RuntimeException('Messaggio Base1024 incompleto');
        $symbols = preg_split('/\s+/u', $payload);
        $expected = (int) ceil(($byteLength * 8) / 10);
        if (count($symbols) !== $expected) throw new RuntimeException('Numero di emoji non valido per Base1024');
        $bytes = [];
        $currentByte = 0;
        $bitsInByte = 0;
        $bitsRead = 0;
        foreach ($symbols as $i => $emoji) {
            if (!isset($this->emojiToIndex[$emoji])) throw new RuntimeException('Emoji sconosciuta');
            $value = $this->emojiToIndex[$emoji] ^ $this->keyWord($i);
            for ($j = 9; $j >= 0 && $bitsRead < $byteLength * 8; $j--) {
                $currentByte = ($currentByte << 1) | (($value >> $j) & 1);
                $bitsInByte++;
                $bitsRead++;
                if ($bitsInByte === 8) {
                    $bytes[] = $currentByte;
                    $currentByte = 0;
                    $bitsInByte = 0;
                }
            }
        }
        if ($bitsRead !== $byteLength * 8 || count($bytes) !== $byteLength) {
            throw new RuntimeException('Messaggio Base1024 incompleto');
        }
        return pack('C*', ...$bytes);
    }

    /** Decodifica compatibile con i messaggi v0.2a (primi 256 simboli). */
    private function decryptLegacy(string $cipher): string {
        if ($cipher === '') return '';
        $symbols = preg_split('/\s+/u', $cipher);
        $bytes = [];
        foreach ($symbols as $p => $emoji) {
            if (!isset($this->emojiToIndex[$emoji])) throw new RuntimeException('Emoji sconosciuta');
            $value = $this->emojiToIndex[$emoji];
            if ($value > 255) throw new RuntimeException('Emoji non valida per un messaggio legacy');
            $bytes[] = $value ^ $this->legacyKeyByte($p);
        }
        return pack('C*', ...$bytes);
    }

    private function legacyKeyByte(int $pos): int {
        $hash = hash('sha256', $this->password . ':' . $pos, true);
        return ord($hash[0]);
    }

    private function keyWord(int $pos): int {
        $hash = hash('sha256', $this->password . ':1024:' . $pos, true);
        return ((ord($hash[0]) << 8) | ord($hash[1])) & 1023;
    }
}
