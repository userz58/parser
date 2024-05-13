<?php

namespace App\DataExtractor\KingTonyCom\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class CatalogCategoryExtractor implements ExtractorInterface
{
    protected string $label = '_category_name';

    protected string $labelHash = '_category_hash';

    protected string $selector = '.bread_box li > a';

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

        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'label' => $node->text(),
                'url' => $node->attr('href'),
            ];
        });

        $last = end($values);

        return [
            $this->label => $this->formatter->format($last['label']),
            $this->labelHash => sha1($last['url']),
        ];
    }
}
