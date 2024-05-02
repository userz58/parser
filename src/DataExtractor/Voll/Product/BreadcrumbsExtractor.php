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
    supportedPageTypes: [PageTypes::INDEX, PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class BreadcrumbsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = '_breadcrumbs';

    protected string $selector = '.breadcrumb a[itemprop=item]';

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
                'uri' => $node->attr('href'),
            ];
        });

        $values = array_filter($values,fn($item) => !in_array($item['uri'], ['/', '/product/', '/catalog/']));

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
