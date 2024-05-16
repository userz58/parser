<?php

namespace App\Event;

use App\Model\Data;
use Symfony\Contracts\EventDispatcher\Event;

class CategoryPageProcessedEvent extends Event
{
    public function __construct(
        private Data $data,
        private string $parserCode,
    )
    {
    }

    public function getData(): Data
    {
        return $this->data;
    }

    public function getParserCode(): string
    {
        return $this->parserCode;
    }
}
