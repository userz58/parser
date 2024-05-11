<?php

namespace App\DataExtractor\Rothenberger\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class StickersExtractor implements ExtractorInterface
{
    protected string $label = 'Наши предложения';

    protected string $selector = '.detail .product-info .product-detail-gallery .stickers > div > div';

    public function __construct(
        private StringFormatter $formatter,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return $node->text();
        });

        if ([] == $values) {
            return [];
        }

        $formatted = array_map(fn($value) => $this->formatter->format($value), $values);
        $formatted = implode(';', $formatted);

        return [$this->label => $formatted];
    }
}
