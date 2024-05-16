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
    protected string $label = 'Видео (код из Youtube)';

    protected string $selector = '#video-tab-pane iframe';

    //private const YOUTUBE_TEMPLATE = '<iframe width="560" height="315" src="https://www.youtube.com/embed/%s" title="Видео - инструмент KingTony" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    private const YOUTUBE_TEMPLATE = '<iframe width="560" height="315" src="https://www.youtube.com/embed/%s?si=D2_aTC2RaJg6bO6n" title="Видео - инструмент KingTony" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(fn(Crawler $node) => $node->attr('src'));

        $codes = [];
        foreach ($values as $url) {
            // regexp https://gist.github.com/ghalusa/6c7f3a00fd2383e5ef33

            //if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match)) {
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[\w\-?&!#=,;]+/[\w\-?&!#=/,;]+/|(?:v|e(?:mbed)?)/|[\w\-?&!#=,;]*[?&]v=)|youtu\.be/)([\w-]{11})(?:[^\w-]|\Z)%i', $url, $match)) {
                $codes[] = $match[1];
            }
        }

        if ([] == $codes) {
            return [];
        }

        $formatted = array_map(fn($v) => sprintf(self::YOUTUBE_TEMPLATE, $v), $codes);

        return [$this->label => $formatted];
    }
}
