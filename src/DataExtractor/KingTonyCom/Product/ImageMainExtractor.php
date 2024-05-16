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
    valueType: ValueTypes::STRING,
)]
class ImageMainExtractor implements ExtractorInterface
{
    protected string $label = 'Изображение';

    protected string $selector = '.swiper:first-child > .swiper-wrapper > .swiper-slide > a[data-fancybox] > img[data-src]';

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
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'https://youtu.be/'));
        $values = array_filter($values, fn($uri) => !str_contains($uri, 'youtube.com'));

        $url = reset($values);

        if (empty($url)) {
            return [];
        }

        $downloaded = str_replace('https://www.kingtony.com/upload', 'kingtonyimages', $url);
        if (!$this->filesystem->fileExists($downloaded)) {
            $this->bus->dispatch(new DownloadImage($url, $downloaded));
            //dump('>>>> ' . $downloaded);
        }

        return [
            //$this->label => $url,
            $this->label => $downloaded,
            //'1Изображение' => $downloaded,
        ];
    }
}
