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
class LinkedArticlesExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Статьи';

    protected string $selector = '.detail .tab-content #articles .item .title a';

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'name' => $node->text(),
                'uri' => $node->attr('href'),
            ];
        });

        if ([] == $values) {
            return [];
        }

        $formatted = [];
        foreach ($values as $value) {
            $url = printf('%s%s', self::BASE_HREF, $value['uri']);
            $key = sha1($url);
            $formatted[$key] = $value['name'];
        }

        return [$this->label => $formatted];
    }
}
