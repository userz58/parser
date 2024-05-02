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
class DescriptionExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://tss.ru';

    const UPLOAD_DIR = '/upload/tss';

    protected string $label = 'Детальное описание';

    protected string $selector = '#product-description-tab-content div div';

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
        $value = iconv(mb_detect_encoding($value, mb_detect_order(), true), "UTF-8", $value);
        //dump($value);
        $value = str_replace("(", " (", $value);
        $value = preg_replace('/[\t\n\s]+/', ' ', $value);
        $value = preg_replace('~>\s+<~', '><', $value);
        $value = preg_replace('~>\s+~', '>', $value);
        $value = preg_replace('~\s+<~', '<', $value);

        // удалить блоки стилей
        $value = preg_replace('/(<style[^>]+>.*?<\/style>)/i', '', $value);
        $value = preg_replace('/(<style>.*?<\/style>)/i', '', $value);

        // удалить ютуб видео
        $value = preg_replace('/<iframe[^>]+>.*?<\/iframe>/i', '', $value);
        $value = preg_replace('/<div[^>]*>\s*ВИДЕО[^<]*<\/div>/i', '', $value);
        //$value = preg_replace('/<div class="title">\s*ВИДЕО[^<]*<\/div>/i', '', $value);

        // заменить в числах , на .
        $value = preg_replace('/(?<=[0-9])+(\,)(?=[0-9]{1,3})/m', '.', $value);
        //dump($value);

        // убрать <i class="fas fa-angle-double-right " aria-hidden="true" style="color: #1491ee;"></i>
        $value = preg_replace('/<i[^>]*class="fa[^"]+"[^>]*><\/i>/', '', $value);


        // очистить заголовки
        // <p class="one_chars_item_title_element">
        // <span style="font-size: 18px; color: #206cb5;"> Расшифровка кодового обозначения</span>
        // </p>
        $value = preg_replace_callback(
            '~<p[^>]+class="one_chars_item_title_element"[^>]*>\K(.+?)(?=</p>)~s',
            function ($m) {
                // todo: заменить на strip_tags
                return preg_replace('~<span[^>]*>(.+?)</span>~', '$1', $m[1]);
            },
            $value
        );

        // <p class="one_chars_item_title_element">
        // Область применения:
        // </p>
        $value = preg_replace('/<p[^>]*class="[^"]*one_chars_item_title_element[^"]*"[^>]*>([^<]+)<\/p>/', '<h3>$1</h3>', $value);
        //--> todo: delete
        //$value = preg_replace('/<p [^<]*?class="[^<]*?one_chars_item_title_element.*?">(.*?)<\/p>/', '<h3>$1</h3>', $value);
        //<-- todo: delete

        // <span style="font-size: 20px; color: #206cb5;"><b>Дизельная электростанция 8 кВт на прицепе серии Prof от ведущего российского производителя TSS</b></span>
        $value = preg_replace('/<span[^>]+>[\s]*<b>(.+)<\/b>[\s]*<\/span>/', '<h4>$1</h4>', $value);

        // <br><b>Какой-то текст</b>
        //$value = preg_replace('/<br><b>([^<]+)<\/b>/', '<h4>$1</h4>', $value);

        // <div class="title">ДОПОЛНИТЕЛЬНО ДОКУПАЕТСЯ:</div>
        $value = preg_replace('/<div class="title">([^<]+)<\/div>/i', "<h3>$1</h3>", $value);

        // <p style="sasda">a b c</p>
        $value = preg_replace('/<p[^>]*style="[^"]+"[^>]*>/i', '<p>', $value);

        //$value = preg_replace('/\s?style=["][^"]*"\s?/i', '', $value);

        // заголовок в таблице <div class="l main-page-projects h2_title">ТЕХНИЧЕСКАЯ ИНФОРМАЦИЯ</div>
        $value = preg_replace('/<div class="[^"]*h2_title[^"]*">([^<]+)<\/div>/i', '<h3>$1</h3>', $value);

        // <ul style="margin-left: 20px;">
        $value = preg_replace('/<ul[\s]+style[^>]+>/i', "<ul>", $value);


        // todo: изменить стили img
        // todo: удалить стили у тегов
        // $value = preg_replace('~\hstyle="[^"]+"~', '', $value);



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

        // скачать pdf-файлы
        preg_match('/http[s]?:\/\/.*?\.pdf/i', $value, $pdfs);
        foreach ($pdfs as $originalPdfLink) {
            if (!str_contains($originalPdfLink, 'http')) {
                $downloadLink = sprintf('%s%s', self::BASE_HREF, $originalPdfLink);
            } else {
                $downloadLink = $originalPdfLink;
            }

            $replaceSrc = str_replace(self::BASE_HREF, '/upload/tss', $originalPdfLink);

            //dump($downloadLink, $replaceSrc);

            $replaceSrc = $this->downloaderFiles->downloadTo($downloadLink, $replaceSrc);

            // заменить в текте ссылки
            $value = str_replace($originalPdfLink, $replaceSrc, $value);
        }

        // замены того то чтолько вручную можно
        $value = str_replace([
            'rel="nofollow noopener"',
            'rel="nofollow"',
            '<strong class="header"></strong>',
            '<strong class="header">',
            '<div class="content">',
            '<ul class="list">',
            '<li class="item">',
            '<span class="text">',
            '<span><br></span>',
            '<span></span>',
            "<br><br>",
            "</ul><br>",
            "<br><ul>",
            "<br><p>",
            "<br></p>",
            "<br></li>",
            "<br></div>",
            "<b>",
            "</b>",
            "КОМПЛЕКТ ПОСТАВКИ, ГАРАНТИЯ ПРОИЗВОДИТЕЛЯ И СЕРВИСНОЕ ОБСЛУЖИВАНИЕ",
            "ПРЕИМУЩЕСТВА ПЕРЕДВИЖНЫХ ДЭС",
            "4 ПЛЮСА ПОКУПКИ У НАС",
            "эксплуатации",
            "https://www.tss.ru/projects/",
            "https://www.tss.ru/",
            ' style="text-align: center;"',
            "<li></li>",
            "<li><strong><br></strong><ul>",
        ], [
            '',
            '',
            '',
            '<strong>',
            '<div>',
            '<ul>',
            '<li>',
            '<span>',
            '',
            '',
            "<br>",
            "</ul>",
            "<ul>",
            "<p>",
            "</p>",
            "</li>",
            "</div>",
            "<strong>",
            "</strong>",
            "Комплект поставки, гарантия и сервисное обслуживание",
            "Преимущества передвижных ДГУ",
            "Преимущества покупки у нас",
            "эксплуатации ",
            "#",
            "/",
            '',
            '',
            '',
        ], $value);

        $value = str_replace([
            "<br><h",
            "<p></p>",
            "<br><br>",
        ], [
            "<h",
            "",
            "<br>",
        ], $value);

        // --> !!! это в самый конец !!!
        $value = preg_replace('~<p></p>~', '', $value);
        $value = preg_replace('~(<img|<div|<a|<p>|<ul>|<h3>)~', "\n$1", $value);
        $value = preg_replace('~(</div>|</p>|<ul>|<\/li>|<\/ul>|</h3>)~', "$1\n", $value);
        $value = preg_replace('/\n+/', "\n", $value);
        $value = preg_replace('/  /', ' ', $value);
        $value = trim($value);
        // <-- !!! это в самый конец !!!

        $value = preg_replace('/<ul>[\n|\s]*<\/ul>/i', '', $value);
        $value = preg_replace('/<span>[\n|\s]*<ul>/i', '<ul>', $value);
        $value = preg_replace('/<\/ul>[\n|\s]*<\/span>/i', '</ul>', $value);
        $value = preg_replace('/<br><table class="h2_t"><tbody><tr><td class="x">[\n|\s]*<h3>([^<]+)<\/h3>[\n|\s]*<\/td><td style="[^"]+">[\n|\s]*<div class="l"><span class="line"><\/span><\/div>[\n|\s]*<\/td><\/tr><\/tbody><\/table>[\n|\s]*/i', "<h3>$1</h3>", $value);

        return $value;
    }
}
