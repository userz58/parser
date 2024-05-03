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
class ModificationsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Модикации';

    protected string $selector = '.detail .tab-content #modifications .module_products_list .item';

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'uri' => $node->filter('meta[itemprop="url"]')->attr('content'),
                'name' => $node->filter('meta[itemprop="name"]')->attr('content'),
            ];
        });

        $formatted = [];
        foreach ($values as $value) {
            $url = printf('%s%s', self::BASE_HREF, $value['uri']);
            $key = sha1($url);
            $formatted[$key] = $value['name'];
        }

        if ([] == $formatted) {
            return [];
        }

        return [$this->label => $formatted];
    }
}
