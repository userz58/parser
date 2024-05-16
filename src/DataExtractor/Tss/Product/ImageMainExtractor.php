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
    valueType: ValueTypes::STRING,
)]
class ImageMainExtractor implements ExtractorInterface
{
    const UPLOAD_DIR = '/upload/tss-images';

    const BASE_HREF = 'https://tss.ru';

    protected string $label = 'Изображение';

    protected string $labelDownloaded = '1Скаченное Изображение';

    protected string $selector = '#thumbs_list #thumbs_list_frame li a';

    public function __construct(
        private DownloaderFiles $downloader,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return $node->attr('href');
        });

        $uri = reset($values);

        if (str_contains($uri, 'no_foto') || empty($uri)) {
            return [];
        }

        $formatted = sprintf('%s%s', self::BASE_HREF, $uri);
        $downloaded = $this->download($formatted);

        return [
            $this->label => $formatted,
            $this->labelDownloaded => $downloaded,
        ];
    }

    private function download(string $url): string
    {
        return $this->downloader->downloadInto($url, self::UPLOAD_DIR);
    }
}
