<?php

namespace App\DataExtractor\Rothenberger\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class ImagesExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = 'Изображения (дополнительные)';

    protected string $selector = '.detail .product-info .product-detail-gallery .product-detail-gallery__link';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [];
        }

        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return $node->attr('href');
        });

        $formatted = $this->format($values);

        if ([] == $formatted) {
            return [];
        }

        return [$this->label => $formatted];
    }

    private function format(array $values): array
    {
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'noimage_product'));
        $values = array_map(fn($value) => sprintf('%s%s', self::BASE_HREF, $value), $values);

        array_shift($values);

        return $values;
    }
}
