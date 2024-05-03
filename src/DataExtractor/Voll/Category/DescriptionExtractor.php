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
class DescriptionExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Описание';

    protected string $selector = '.catalog_page .text_after_items';

    public function __construct(
        private StringFormatter $formatter,
        // downloader
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() == 0) {
            return [$this->label => null];
        }

        $description = $crawler->filter($this->selector)->html();

        $formatted = $this->formatText($description);

        return [$this->label => $formatted];
    }


    private function formatText(string $value): string
    {
        $value = iconv(mb_detect_encoding($value, mb_detect_order(), true), "UTF-8", $value);
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);
        $value = str_replace([
            "(",
            "&nbsp;",
            "&nbsp;</",
            ";<",
            ".;",
            "h2>",
        ], [
            " (",
            ";",
            "</",
            "<",
            ".",
            "h3>",
        ], $value);
        $value = preg_replace('/[\t\n\s]+/', ' ', $value);
        $value = preg_replace('~>\s+<~', '><', $value);
        $value = preg_replace('~>\s+~', '>', $value);
        $value = preg_replace('~\s+<~', '<', $value);

        // --> !!! это в самый конец !!!
        $value = preg_replace('~<p></p>~', '', $value);
        $value = preg_replace('~<div></div>~', '', $value);
        $value = preg_replace('~(<img|<div|<a|<p>|<ul>|<h3>)~', "\n$1", $value);
        $value = preg_replace('~(</div>|</p>|<ul>|<\/li>|<\/ul>|</h3>)~', "$1\n", $value);
        $value = preg_replace('/\n+/', "\n", $value);
        $value = preg_replace('/  /', ' ', $value);
        $value = trim($value);
        // <-- !!! это в самый конец !!!
        return $value;
    }
}
