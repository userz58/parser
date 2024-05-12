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
class LinkedProductsExtractor implements ExtractorInterface
{
    protected string $label = 'Рекомендованные товары (связанные товары)';

    protected string $selector = 'div:contains("Recommended Products") + div > .p_out .p_slide > a';

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
