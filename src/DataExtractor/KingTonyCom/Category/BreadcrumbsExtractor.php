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
    valueType: ValueTypes::LIST,
)]
class BreadcrumbsExtractor implements ExtractorInterface
{
    protected string $label = '_breadcrumbs';

    protected string $selector = '.bread_box li > a';

    public function __construct(
        private StringFormatter  $formatter,
        private SluggerInterface $slugger,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'label' => $node->text(),
                'url' => $node->attr('href'),
            ];
        });

        return [$this->label => $this->formatValues($values)];
    }

    private function formatValues(array $values): array
    {
        $values = array_filter($values, fn($item) => !in_array($item['url'], ['https://www.kingtony.com/index.php', 'https://www.kingtony.com/product.php']));

        $formatted = [];

        foreach ($values as $value) {
            $label = $this->formatter->format($value['label']);
            $url = $value['url'];

            $formatted += [$label => $url];
        }

        return $formatted;
    }
}
