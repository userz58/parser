<?php

namespace App\DataExtractor\Rothenberger\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class LinkedDopPartsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = 'Рекомендованные товары (запчасти/принадлежности)';

    protected string $selectorP = '.detail .tab-content #dopparts .items .item';

    protected string $selectorA = '.detail .tab-content #accessories .items .item';

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
                'name' => $node->filter('.item-title a span')->text(),
                'uri' => $node->filter('.item-title a')->attr('href'),
            ];
        });

        $accessories = $crawler->filter($this->selectorA)->each(function (Crawler $node, $i) {
            return [
                'name' => $node->filter('.item-title a span')->text(),
                'uri' => $node->filter('.item-title a')->attr('href'),
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
