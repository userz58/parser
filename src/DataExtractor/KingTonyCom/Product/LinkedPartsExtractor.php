<?php

namespace App\DataExtractor\KingTonyCom\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class LinkedPartsExtractor implements ExtractorInterface
{
    protected string $label = 'Запчасти и принадлежности (связанные товары)';

    protected string $selector = '#profile-tab-pane a';

    public function __construct(
        private Pool $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'name' => $node->filter('h3,h4')->text(),
                'url' => $node->attr('href'),
            ];
        });

        $formatted = [];
        foreach ($values as $value) {
            $url = $value['url'];
            $formatted[sha1($url)] = trim($value['name']);
            $this->pool->add($url); // добавить в очередь на скачивание
        }

        if ([] == $formatted) {
            return [];
        }

        return [$this->label => $formatted];
    }
}
