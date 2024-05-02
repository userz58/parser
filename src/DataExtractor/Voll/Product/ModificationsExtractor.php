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
            $productUrl = $node->filter('meta[itemprop="url"]');
            $productName = $node->filter('meta[itemprop="name"]');

            return [
                'url' => $productUrl,
                'name' => $productName,
            ];
        });

        $formatted = $this->format($values);

        if ([] == $formatted) {
            return [];
        }

        return [$this->label => $formatted];
    }

    private function format(array $values): array
    {
        // todo: ...

        return $values;
    }
}
