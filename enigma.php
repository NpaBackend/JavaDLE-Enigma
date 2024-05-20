<?php

class Enigma {
    private array $rotors;
    private string $reflector;
    private array $rotorPositions;
    private array $initialRotorPositions;
    private array $rotorMappings;
    private string $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Enigma constructor.
     * @param array $rotors - массив строк, каждая из которых представляет ротор
     * @param string $reflector - строка, представляющая отражатель
     * @param array $rotorPositions - массив начальных позиций роторов
     */
    public function __construct(array $rotors, string $reflector, array $rotorPositions) {
        $this->rotors = $rotors;
        $this->reflector = $reflector;
        $this->rotorPositions = $rotorPositions;
        $this->initialRotorPositions = $rotorPositions; // сохраняем начальные позиции
        $this->initializeRotorMappings();
    }

    /**
     * Инициализация маппинга роторов на основе текущих позиций.
     */
    private function initializeRotorMappings(): void {
        $this->rotorMappings = [];
        for ($i = 0; $i < count($this->rotors); $i++) {
            $this->rotorMappings[] = $this->createRotorMapping($this->rotors[$i], $this->rotorPositions[$i]);
        }
    }

    /**
     * Создание маппинга ротора на основе текущей позиции.
     * @param string $rotor - строка, представляющая ротор
     * @param string $position - начальная позиция ротора
     * @return array - ассоциативный массив маппинга ротора
     */
    private function createRotorMapping(string $rotor, string $position): array {
        $mapping = [];
        $offset = array_search($position, str_split($this->alphabet));
        for ($i = 0; $i < 26; $i++) {
            $index = ($i + $offset) % 26;
            $mapping[$this->alphabet[$i]] = $rotor[$index];
        }
        return $mapping;
    }

    /**
     * Поворот роторов на одну позицию.
     */
    private function rotateRotors(): void {
        for ($i = 0; $i < count($this->rotors); $i++) {
            $this->rotorPositions[$i] = $this->alphabet[(array_search($this->rotorPositions[$i], str_split($this->alphabet)) + 1) % 26];
            $this->rotorMappings[$i] = $this->createRotorMapping($this->rotors[$i], $this->rotorPositions[$i]);
            if ($this->rotorPositions[$i] != 'A') {
                break;
            }
        }
    }

    /**
     * Отражение символа через отражатель.
     * @param string $char - символ для отражения
     * @return string - отраженный символ
     */
    private function reflect(string $char): string {
        return $this->reflector[array_search($char, str_split($this->alphabet))];
    }

    /**
     * Проход символа через роторы.
     * @param string $char - символ для прохождения через роторы
     * @param bool $reverse - флаг для обратного прохождения через роторы
     * @return string - результирующий символ
     */
    private function passThroughRotors(string $char, bool $reverse = false): string {
        if ($reverse) {
            for ($i = count($this->rotorMappings) - 1; $i >= 0; $i--) {
                $char = array_search($char, $this->rotorMappings[$i]);
            }
        } else {
            for ($i = 0; $i < count($this->rotorMappings); $i++) {
                $char = $this->rotorMappings[$i][$char];
            }
        }
        return $char;
    }

    /**
     * Шифрование текста с использованием машины Enigma.
     * @param string $text - текст для шифрования
     * @return string - зашифрованный текст
     */
    public function encrypt(string $text): string {
        $text = strtoupper($text);
        $encryptedText = '';
        for ($i = 0; $i < strlen($text); $i++) {
            if (strpos($this->alphabet, $text[$i]) !== false) {
                $this->rotateRotors();
                $char = $text[$i];
                $char = $this->passThroughRotors($char);
                $char = $this->reflect($char);
                $char = $this->passThroughRotors($char, true);
                $encryptedText .= $char;
            } else {
                $encryptedText .= $text[$i];
            }
        }
        return $encryptedText;
    }

    /**
     * Сброс роторов в начальные позиции.
     */
    public function resetRotors(): void {
        $this->rotorPositions = $this->initialRotorPositions;
        $this->initializeRotorMappings();
    }
}

// Пример использования:
$rotors = [
    'EKMFLGDQVZNTOWYHXUSPAIBRCJ',  // Ротор I
    'AJDKSIRUXBLHWTMCQGZNPYFVOE',  // Ротор II
    'BDFHJLCPRTXVZNYEIWGAKMUSQO'   // Ротор III
];

$reflector = 'YRUHQSLDPXNGOKMIEBFZCWVJAT';  // Отражатель
$rotorPositions = ['A', 'A', 'A'];  // Начальные позиции роторов

$enigma = new Enigma($rotors, $reflector, $rotorPositions);

echo "<pre>";

$plaintext = "hello enigma";

$ciphertext = $enigma->encrypt($plaintext);
echo "Зашифрованный текст: $ciphertext";
echo "</pre>";

$enigma->resetRotors(); // Сбрасываем роторы в начальные позиции

echo "<pre>";

$encryptedText = "QHHHQ IWKCCE";

$decryptedText = $enigma->encrypt($encryptedText);
echo "Расшифрованный текст: $decryptedText";
echo "</pre>";
?>