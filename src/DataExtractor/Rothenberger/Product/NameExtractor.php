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
    valueType: ValueTypes::STRING,
)]
class NameExtractor implements ExtractorInterface
{
    protected string $label = 'Наименование';

    protected string $selector = 'h1#pagetitle';

    public function __construct(
        private StringFormatter $formatter,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $value = $crawler->filter($this->selector)->text();
        $value = $this->formatter->format($value);

        return [$this->label => $value];
    }
}
