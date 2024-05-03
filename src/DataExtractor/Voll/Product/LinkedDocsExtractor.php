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
            return [
                'name' => $node->text(),
                'uri' => $node->attr('href'),
            ];
        });

        $values = array_filter($values, fn($item) => '/landings/catalog-pdf/' !== $item['uri']);

        $formatted = [];
        foreach ($values as $value) {
            $url = printf('%s%s', self::BASE_HREF, $value['uri']);
            $key = sha1($url);
            $formatted[$key] = $value['name'];

            // todo: download;
        }

        if ([] == $formatted) {
            return [];
        }

        return [$this->label => $formatted];
    }
}
