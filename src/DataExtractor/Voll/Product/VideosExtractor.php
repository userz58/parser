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
class VideosExtractor implements ExtractorInterface
{
    protected string $label = 'Видео (код из Youtube)';

    protected string $selector = '.detail .tab-content #video .video_body iframe';

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return $node->attr('src');
        });

        if ([] == $values) {
            return [];
        }

        return [$this->label => $values];
    }
}
