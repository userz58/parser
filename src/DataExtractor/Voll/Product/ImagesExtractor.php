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
class ImagesExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Изображения (дополнительные)';

    protected string $selector = '.detail .galery .slides .item a.fancybox';

    public function extract(Crawler $crawler): array
    {
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
        //$values = array_filter($values, fn($uri) => !str_contains($uri, 'no_foto'));
        $values = array_map(fn($value) => sprintf('%s%s', self::BASE_HREF, $value), $values);

        array_shift($values);

        return $values;
    }
}
