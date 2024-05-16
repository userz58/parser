<?php

namespace App\DataExtractor\KingTonyCom\CategoryIndex;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::INDEX],
    valueType: ValueTypes::LIST,
)]
class ChildrenExtractor implements ExtractorInterface
{
    protected string $label = '_children';

    protected string $selector = '.p_items2 > a';

    public function __construct(
        private Pool $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        dump(1);
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'name' => $node->filter('h3')->text(),
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
