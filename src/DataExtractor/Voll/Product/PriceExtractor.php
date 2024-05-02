<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class PriceExtractor implements ExtractorInterface
{
    protected string $label = 'Цена';

    protected string $selector = '.detail .info .price_val[itemprop="price"]';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [];
        }

        $value = $crawler->filter($this->selector)->attr('content');
        $formatted = round($value);

        return [$this->label => $formatted];
    }
}
