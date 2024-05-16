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
class VideosExtractor implements ExtractorInterface
{
    protected string $label = 'Видео (только ссылка)';

    protected string $selector = '#video-tab-pane iframe';

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(fn(Crawler $node) => $node->attr('src'));

        $codes = [];
        foreach ($values as $url) {
            //if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match)) {
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[\w\-?&!#=,;]+/[\w\-?&!#=/,;]+/|(?:v|e(?:mbed)?)/|[\w\-?&!#=,;]*[?&]v=)|youtu\.be/)([\w-]{11})(?:[^\w-]|\Z)%i', $url, $match)) {
                $codes[] = $match[1];
            }
        }

        if ([] == $codes) {
            return [];
        }

        $formatted = array_map(fn($item) => sprintf('https://youtu.be/%s', $item), $codes);

        return [$this->label => $formatted];
    }
}
