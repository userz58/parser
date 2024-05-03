<?php

namespace App\DataExtractor\Voll\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
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
