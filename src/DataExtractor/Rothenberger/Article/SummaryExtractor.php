<?php

namespace App\DataExtractor\Rothenberger\Article;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Downloader\DownloaderFiles;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::ARTICLE],
    valueType: ValueTypes::STRING,
)]
class SummaryExtractor implements ExtractorInterface
{
    protected string $label = 'Анонс';

    protected string $selector = '.news .detail_wrapper .introtext[itemprop="description"]';

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() == 0) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $description = $crawler->filter($this->selector)->html();
        $formatted = $this->formatText($description);

        return [$this->label => $formatted];
    }

    private function formatText(string $value): string
    {
        $value = iconv(mb_detect_encoding($value, mb_detect_order(), true), "UTF-8", $value);
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);
        $value = preg_replace('/[\t\n\s]+/', ' ', $value);
        $value = trim($value);

        return $value;
    }
}
