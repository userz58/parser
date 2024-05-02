<?php

namespace App\ValueObject;

class ProductData extends AbstractData
{
    public const PAGE_NAME = 'Товары';

    public function getPageName(): string
    {
        return self::PAGE_NAME;
    }
}
