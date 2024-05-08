<?php

namespace App\DataExtractor\Voll\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::STRING,
)]
class ShortDescriptionExtractor implements ExtractorInterface
{
    protected string $label = 'Краткое описание';

    protected string $selector = '.catalog_page .introtext';

    public function __construct(
        private StringFormatter $formatter,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() == 0) {
            return [$this->label => null];
        }

        $description = $crawler->filter($this->selector)->html();

        $formatted = $this->formatter->format($description);

        return [$this->label => $formatted];
    }
}
