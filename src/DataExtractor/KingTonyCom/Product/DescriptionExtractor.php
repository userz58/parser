<?php

namespace App\DataExtractor\KingTonyCom\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class DescriptionExtractor implements ExtractorInterface
{
    protected string $label = 'Описание';

    protected string $selector = '.p_d_box > ul li';

    public function __construct(
        private StringFormatter $formatter,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        $list = $crawler->filter($this->selector)->each(fn(Crawler $node) => $node->text());

        $values = array_filter($list, fn($l) => !empty($l));

        $formatted = "<ul>";
        foreach ($values as $value) {
            $formatted .= sprintf("<li>%s</li>\n", $value);
        }
        $formatted .= "</ul>";

        return [$this->label => $formatted];
    }
}
