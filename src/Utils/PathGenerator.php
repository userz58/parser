<?php

namespace App\Utils;

/**
 * Генерирование уникального имени файла по URL
 */
class PathGenerator
{
    public function generate(string $url): string
    {
        $uuid = sha1($url);

        return sprintf('%s/%s/%s/%s/%s', $uuid[0], $uuid[1], $uuid[2], $uuid[3], $uuid);
    }
}
