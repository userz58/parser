<?php

namespace App\DataExtractor\KingTonyCom\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Message\DownloadImage;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use League\Flysystem\Filesystem;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class ImagesExtractor implements ExtractorInterface
{
    protected string $label = 'Изображения (дополнительные)';

    protected string $selector = '.swiper:first-child > .swiper-wrapper > .swiper-slide > a[data-fancybox]:not([href^="https://youtu.be/"]) > img[data-src]';

    public function __construct(
        private MessageBusInterface $bus,
        private Filesystem          $filesystem,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [];
        }

        $values = $crawler->filter($this->selector)->each(fn(Crawler $node) => $node->attr('data-src'));

        if ([] == $formatted = $this->format($values)) {
            return [];
        }

        $downloaded = [];
        foreach ($formatted as $url) {
            $downloadedFilepath = str_replace('https://www.kingtony.com/upload', 'kingtonyimages', $url);

            if (!$this->filesystem->fileExists($downloadedFilepath)) {
                $this->bus->dispatch(new DownloadImage($url, $downloadedFilepath));
            }

            $downloaded[] = $downloadedFilepath;
        }

        return [
            //$this->label => $formatted,
            $this->label => $downloaded,
        ];
    }

    private function format(array $values): array
    {
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'https://youtu.be/'));
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'youtube.com'));

        array_shift($values);

        return $values;
    }
}
