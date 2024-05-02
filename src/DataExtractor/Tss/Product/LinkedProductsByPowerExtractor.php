<?php

namespace App\DataExtractor\Tss\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\TssParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [TssParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class LinkedProductsByPowerExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://tss.ru';

    protected string $label = 'Аналоги по мощности';

    protected string $selector = '[itemtype="https://schema.org/Product"] .h2_title:contains("Аналоги по мощности") + .product_list .product-name';
    //protected string $selector = '.h2_title:contains("Аналоги по мощности") + .slide_element_dop .product_list .product-name';

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
                'name' => $node->attr('title'),
                'uri' => $node->attr('href'),
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
