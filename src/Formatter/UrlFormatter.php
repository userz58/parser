<?php

namespace App\Formatter;

class UrlFormatter implements FormatterInterface
{
    public function format(string|null $str): ?string
    {
        if (null === $str || $str === '') {
            return null;
        }

        $str = iconv(mb_detect_encoding($str), 'UTF-8', $str);
        //$str = mb_convert_encoding($str, 'ISO-8859-1');
        //$str = mb_convert_encoding($str, 'UTF-8');
        $str = urldecode($str);
        $str = rawurlencode($str);
        $str = str_replace(['%3A', '%2F',], [':', '/',], $str);

        return $str;
    }
}
