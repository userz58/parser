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
class LinkedPartsExtractor extends LinkedProductsExtractor
{
    protected string $label = 'Запчасти';

    protected string $selector = '.detail .tab-content #dopparts .module_products_list .item';
    // accessories
    // protected string $selectorA = '.detail .tab-content #accessories .module_products_list .item';
}
