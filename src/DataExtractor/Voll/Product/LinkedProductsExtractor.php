<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
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
class LinkedProductsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Модикации';

    protected string $selector = '.detail .tab-content #modifications .module_products_list .item';

    public function __construct(
        private StringFormatter $formatter,
        private Pool            $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'name' => $node->filter('meta[itemprop="name"]')->attr('content'),
                'uri' => $node->filter('meta[itemprop="url"]')->attr('content'),
            ];
        });

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

        return [$this->label => $formatted];
    }
}
