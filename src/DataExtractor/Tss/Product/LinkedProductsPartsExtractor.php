<?php

namespace App\DataExtractor\Tss\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\TssParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [TssParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class LinkedProductsPartsExtractor extends LinkedProductsExtractor
{
    protected string $label = 'Запчасти';

    protected string $selector = '#product-zapchasti-tab-content table tr td:nth-child(2) a:not(.btn)';
}
