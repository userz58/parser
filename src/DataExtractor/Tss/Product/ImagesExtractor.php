<?php

namespace App\DataExtractor\Tss\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Downloader\DownloaderFiles;
use App\Parser\TssParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [TssParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class ImagesExtractor implements ExtractorInterface
{
    const UPLOAD_DIR = '/upload/tss-images';

    const BASE_HREF = 'https://tss.ru';

    protected string $label = 'Изображения (дополнительные)';
    protected string $labelDownloaded = '1Скаченные Изображения (дополнительные)';

    protected string $selector = '#thumbs_list #thumbs_list_frame li a';

    public function __construct(
        private DownloaderFiles $downloader,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return $node->attr('href');
        });

        $formatted = $this->format($values);

        if ([] == $formatted) {
            return [];
        }

        $downloaded = array_map(fn($item) => $this->download($item), $formatted);

        return [
            $this->label => $formatted,
            $this->labelDownloaded => $downloaded,
        ];
    }

    private function format(array $values): array
    {
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'no_foto'));
        $values = array_map(fn($value) => sprintf('%s%s', self::BASE_HREF, $value), $values);
        array_shift($values);
        $values = array_filter($values, fn($url) => $url !== self::BASE_HREF);

        return $values;
    }

    private function download(string $url): string
    {
        return $this->downloader->downloadInto($url, self::UPLOAD_DIR);
    }
}
