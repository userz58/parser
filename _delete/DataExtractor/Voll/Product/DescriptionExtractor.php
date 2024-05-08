<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Downloader\DownloaderFiles;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class DescriptionExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    const UPLOAD_DIR = '/upload/voll-ru';

    protected string $label = 'Детальное описание';

    protected string $selector = '.detail #desc .content[itemprop="description"]';

    public function __construct(
        private DownloaderFiles $downloaderFiles,
    )
    {
    }


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
        //dump($value);
        $value = iconv(mb_detect_encoding($value, mb_detect_order(), true), "UTF-8", $value);
        $value = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $value);
        $value = str_replace([
            "<b> ",
            " </b>",
            "(",
            "&nbsp;</",
            ">&nbsp;",
        ], [
            "<b>",
            "</b>",
            " (",
            "</",
            ">",
        ], $value);

        $value = preg_replace('/[\t\n\s]+/', ' ', $value);
        $value = preg_replace('~>\s+<~', '><', $value);
        $value = preg_replace('~>\s+~', '>', $value);
        $value = preg_replace('~\s+<~', '<', $value);

        // скачать картинки и заменить пути
        preg_match_all('/src=\"(.*?)\"/', $value, $images);
        foreach ($images[1] as $originalSrc) {
            // если пустая - не скачивать
            if (empty($originalSrc)) {
                continue;
            }

            if (!str_contains($originalSrc, 'https:')) {
                $downloadSrc = sprintf('%s%s', self::BASE_HREF, $originalSrc);
            } else {
                $downloadSrc = $originalSrc;
            }

            // если не получилось скачать
            try {
                $replaceSrc = $this->downloaderFiles->downloadInto($downloadSrc, self::UPLOAD_DIR);
            } catch (\Exception $exception) {
                continue;
            }

            // заменить в текте ссылки на изображения
            $value = str_replace($originalSrc, $replaceSrc, $value);
        }

        $value = str_replace([
                '</b>',
                '</strong>',
            ], [
                '</b> ',
                '</strong> ',
            ], $value);

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
