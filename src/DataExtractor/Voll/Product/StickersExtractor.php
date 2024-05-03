<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class StickersExtractor implements ExtractorInterface
{
    protected string $label = '_hit';

    protected string $selector = '.detail .galery .stickers > .stickers-wrapper > div';

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
