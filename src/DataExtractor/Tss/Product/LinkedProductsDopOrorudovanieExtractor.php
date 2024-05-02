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
class LinkedProductsDopOrorudovanieExtractor extends LinkedProductsExtractor
{
    protected string $label = 'Дополнительное оборудование';

    protected string $selector = '#product-dop-oborydovanie-tab-content .one_chars_item_title_element a:last-child';
}
