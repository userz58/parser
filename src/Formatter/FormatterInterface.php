<?php

namespace App\Formatter;

interface FormatterInterface
{
    public function format(string|null $str): ?string;
}
