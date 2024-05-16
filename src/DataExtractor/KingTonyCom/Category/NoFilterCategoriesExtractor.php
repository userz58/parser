<?php

namespace App\DataExtractor\KingTonyCom\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::LIST,
)]
class NoFilterCategoriesExtractor implements ExtractorInterface
{
    public function __construct(
        private Pool             $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        // если нет фильтра .p_list, то можно скачивать подкатегории
        // .p_title > h3 > a

        //if (0 == $crawler->filter('.p_list li')->count()) {
        //}

        // если есть фильтр категорий, то не брать товары

        if ($crawler->filter('.p_list li > a')->count() > 0) {
            return [];
        }

        // если есть несколько подкатегорий, то не брать товары
        if ($crawler->filter('.p_title > h3 > a')->count() > 1) {
            return [];
        }

        // если URL подкатегории не совпадает с текущим
        if ($crawler->filter('.p_title > h3')->count() > 0) {
            if ($crawler->filter('.p_title > h3 > a')->first()->attr('href') !== $crawler->getUri()) {
                return [];
            }
        }

        $urls = $crawler->filter('.p_slide > a')->each(fn(Crawler $node) => $node->attr('href'));
        $urls = array_filter($urls, fn($url) => str_contains($url, 'https://www.kingtony.com/catalogs'));
        foreach ($urls as $url) {
                $this->pool->insert($url);
        }

        return [];
    }
}
