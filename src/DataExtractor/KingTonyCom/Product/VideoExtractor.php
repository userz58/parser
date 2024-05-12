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
    valueType: ValueTypes::LIST,
)]
class VideoExtractor implements ExtractorInterface
{
    protected string $label = 'Видео (удалить)';

    //protected string $selector = '.swiper .swiper-slide > a[href="*youtu*"]';
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

    private function format(array $values): array
    {
        $values = array_filter($values, fn($uri) => str_contains($uri, 'https://youtu.be/'));

        //foreach ($values as $url) {
        //    //if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match)) {
        //    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[\w\-?&!#=,;]+/[\w\-?&!#=/,;]+/|(?:v|e(?:mbed)?)/|[\w\-?&!#=,;]*[?&]v=)|youtu\.be/)([\w-]{11})(?:[^\w-]|\Z)%i', $url, $match)) {
        //        $codes[] = $match[1];
        //    }
        //}

        return $values;
    }
}
