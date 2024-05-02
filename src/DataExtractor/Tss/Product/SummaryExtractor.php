<?php

namespace App\DataExtractor\Tss\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\TssParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [TssParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class SummaryExtractor implements ExtractorInterface
{
    protected string $label = 'Анонс';

    protected string $selector = '.product_attributes .opis [itemprop="description"]';

    /**
     * @param string $label
     */
    public function __construct(
        private StringFormatter $formatter,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() == 0) {
            return [];
        }

        $shortDescription = $crawler->filter($this->selector)->html();
        $formatted = $this->formatter->format($shortDescription);

        return [$this->label => $formatted];
    }

}
