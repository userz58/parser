<?php

namespace App\DataExtractor\KingTonyCom\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class ImageMainExtractor implements ExtractorInterface
{
    protected string $label = 'Изображение';

    protected string $selector = '.swiper .swiper-slide > a';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [];
        }

        $values = $crawler->filter($this->selector)->each(fn(Crawler $node) => $node->attr('href'));

        $formatted = $this->format($values);

        if ([] == $formatted) {
            return [];
        }

        return [$this->label => $formatted];
    }

    private function format(array $values): string
    {
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'https://youtu.be/'));
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'youtube.com'));

        return reset($values);
    }
}
