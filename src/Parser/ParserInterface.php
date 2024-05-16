<?php

namespace App\Parser;

interface ParserInterface
{
    public function parse(): void;

    public function getCode(): string;

    public function getBaseHref(): string;
}
