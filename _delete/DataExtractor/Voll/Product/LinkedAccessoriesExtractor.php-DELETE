<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class LinkedAccessoriesExtractor extends LinkedProductsExtractor
{
    protected string $label = 'Комплектующие';

    protected string $selector = '.detail .tab-content #accessories .module_products_list .item';
}
