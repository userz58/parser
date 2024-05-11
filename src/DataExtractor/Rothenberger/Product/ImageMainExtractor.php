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
    valueType: ValueTypes::STRING,
)]
class ImageMainExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = 'Изображение';

    protected string $selector = '.detail .product-info .product-detail-gallery .product-detail-gallery__link';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            //throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
            dump('Нет фото');
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

    private function format(array $values): string
    {
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'noimage_product'));

        return sprintf('%s%s', self::BASE_HREF, reset($values));
    }
}
