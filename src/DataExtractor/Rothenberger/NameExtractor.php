<?php

namespace App\DataExtractor\Rothenberger;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Parser\RothenbergerParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY, PageTypes::ARTICLE],
    valueType: ValueTypes::STRING,
)]
class NameExtractor implements ExtractorInterface
{
    protected string $label = 'Название';

    protected string $selector = 'h1#pagetitle';

    public function __construct(
        private StringFormatter $formatter,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() == 0) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $title = $crawler->filter($this->selector)->html();

        $formatted = $this->formatter->format($title);

        return [$this->label => $formatted];
    }
}
