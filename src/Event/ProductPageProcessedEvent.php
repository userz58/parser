<?php

namespace App\Event;

use App\Model\Data;
use Symfony\Contracts\EventDispatcher\Event;

class ProductPageProcessedEvent extends Event
{
    public function __construct(
        private Data $data,
    )
    {
    }

    public function getData(): Data
    {
        return $this->data;
    }
}
