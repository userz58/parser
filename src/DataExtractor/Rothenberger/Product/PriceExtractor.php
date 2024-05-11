<?php

namespace App\DataExtractor\Rothenberger\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class PriceExtractor implements ExtractorInterface
{
    protected string $label = 'Цена';

    protected string $selector = '.detail .product-action .info_item .price[data-value]';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [];
        }

        $value = $crawler->filter($this->selector)->attr('data-value');
        $formatted = round($value);

        return [$this->label => $formatted];
    }
}
