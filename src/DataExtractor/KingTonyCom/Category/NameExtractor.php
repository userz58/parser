<?php

namespace App\DataExtractor\KingTonyCom\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::STRING,
)]
class NameExtractor implements ExtractorInterface
{
    protected string $label = 'Название';

    protected string $selector = '.bread_box li';

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

        $values = $crawler->filter($this->selector)->each(fn(Crawler $node) => $node->text());
        $values = array_filter($values, fn($item) => !empty($item));
        $name = $this->formatter->format(end($values));

        return [$this->label => $name];
    }
}
