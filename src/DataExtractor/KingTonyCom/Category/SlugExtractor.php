<?php

namespace App\DataExtractor\KingTonyCom\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::STRING,
)]
class SlugExtractor implements ExtractorInterface
{
    protected string $label = '_slug';

    protected string $selector = '.bread_box li';

    public function __construct(
        private SluggerInterface $slugger,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $values = $crawler->filter($this->selector)->each(fn(Crawler $node) => $node->text());

        $formatted = $this->formatValues($values);

        return [$this->label => $formatted];
    }

    private function formatValues(array $values): string
    {
        $values = array_filter($values, fn($item) => !empty($item));
        $values = array_filter($values, fn($item) => !in_array($item, ['HOME', 'Home', 'Products']));
        $values = array_map(fn($item) => $this->slugger->slug($item), $values);

        return implode('/', $values);
    }
}
