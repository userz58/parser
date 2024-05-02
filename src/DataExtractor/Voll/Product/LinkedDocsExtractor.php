<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class LinkedDocsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Документы';

    protected string $selector = '.detail .tab-content #docs .pdf a';

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            //td
            return [
                'name' => $node->text(),
                'uri' => $node->attr('href'),
            ];
        });

        $formatted = $this->format($values);

        if ([] == $formatted) {
            return [];
        }

        return [$this->label => $formatted];
    }

    private function format(array $values): array
    {
        //$values = array_map(fn($value) => sprintf('%s%s', self::BASE_HREF, $value), $values);

        return $values;
    }
}
