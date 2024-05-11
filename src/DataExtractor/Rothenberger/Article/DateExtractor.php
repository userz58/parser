<?php

namespace App\DataExtractor\Rothenberger\Article;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::ARTICLE],
    valueType: ValueTypes::STRING,
)]
class DateExtractor implements ExtractorInterface
{
    protected string $label = 'Дата';

    protected string $selector = '.news .detail_wrapper .period-block .date';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [$this->label => null];
        }

        $value = $crawler->filter($this->selector)->text();

        return [$this->label => $value];
    }
}
