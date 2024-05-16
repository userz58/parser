<?php

namespace App\EventListener;

use App\Event\ProductPageProcessedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ProductPageProcessedEventListener
{
    public function __invoke(ProductPageProcessedEvent $event): void
    {
        // todo: ...
    }
}
