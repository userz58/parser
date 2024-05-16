<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ParserFinishedEvent extends Event
{
    public function __construct(
        private string $parserCode,
    )
    {
    }

    public function getParserCode(): string
    {
        return $this->parserCode;
    }
}
