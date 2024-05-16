<?php

namespace App\Formatter;

use function Symfony\Component\Translation\t;

/**
 * Удалить:
 * лишние пробелы
 * спецсимволы и знаки
 *
 */
class StringFormatter implements FormatterInterface
{
    public function format(string|null $str): ?string
    {
        if (null === $str || $str === '') {
            return null;
        }

        $str = str_replace(["™", "®", "©", "•"], null, $str);
        $str = preg_replace('/\s+/', ' ', $str); // удалить двойные пробелы
        $str = trim($str);

        return $str;
    }
}
