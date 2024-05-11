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
    supportedPageTypes: [PageTypes::INDEX, PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class BreadcrumbsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = '_breadcrumbs';

    protected string $selector = '#navigation .breadcrumbs > .breadcrumbs__item > a.breadcrumbs__link';

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
                'label' => $node->filter('.breadcrumbs__item-name')->text(),
                'uri' => $node->attr('href'),
            ];
        });

        $values = array_filter($values,fn($item) => !in_array($item['uri'], ['/', '/catalog/']));

        $formatted = [];
        foreach ($values as $value) {
            $formatted += $this->formatValue($value['label'], $value['uri']);
        }

        return [$this->label => $formatted];
    }

    private function formatValue(string $label, string $uri): array
    {
        $label = $this->formatter->format($label);
        $url = sprintf('%s%s', self::BASE_HREF, $uri);

        return [$label => $url];
    }
}
