<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\Formatter\StringFormatter;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class LinkedPartsExtractor
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Запчасти';

    protected string $selectorP = '.detail .tab-content #dopparts .module_products_list .item';

    protected string $selectorA = '.detail .tab-content #accessories .module_products_list .item';


    public function __construct(
        private StringFormatter $formatter,
        private Pool            $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        $parts = $crawler->filter($this->selectorP)->each(function (Crawler $node, $i) {
            return [
                'name' => $node->filter('meta[itemprop="name"]')->attr('content'),
                'uri' => $node->filter('meta[itemprop="url"]')->attr('content'),
            ];
        });

        $accessories = $crawler->filter($this->selectorA)->each(function (Crawler $node, $i) {
            return [
                'name' => $node->filter('meta[itemprop="name"]')->attr('content'),
                'uri' => $node->filter('meta[itemprop="url"]')->attr('content'),
            ];
        });

        $values = array_merge($parts, $accessories);

        $formatted = [];

        foreach ($values as $value) {
            $url = sprintf('%s%s', self::BASE_HREF, $value['uri']);
            $hash = sha1($url);
            $formatted[$hash] = $this->formatter->format($value['name']);

            // добавить в очередь на скачивание
            $this->pool->add($url);
        }

        if ([] == $formatted) {
            return [];
        }

        $formatted = array_unique($formatted);

        return [$this->label => $formatted];
    }
}
