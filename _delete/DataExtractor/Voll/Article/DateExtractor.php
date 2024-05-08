<?php

namespace App\DataExtractor\Voll\Article;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::ARTICLE],
    valueType: ValueTypes::STRING,
)]
class DateExtractor implements ExtractorInterface
{
    protected string $label = 'Дата';

    protected string $selector = '.detail .period .date';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [$this->label => null];
        }

        $value = $crawler->filter($this->selector)->text();

        return [$this->label => $value];
    }
}
