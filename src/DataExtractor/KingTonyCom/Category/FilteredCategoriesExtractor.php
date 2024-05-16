<?php

namespace App\DataExtractor\KingTonyCom\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::LIST,
)]
class FilteredCategoriesExtractor implements ExtractorInterface
{
    public function __construct(
        private Pool $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        // если выбран фильтр подкатегории
        // нойти отфильтрованные категории
        if ($crawler->filter('.p_list li.active a')->count() > 0) {
            $urls = $crawler->filter('.p_slide > a')->each(fn(Crawler $node) => $node->attr('href'));
            //отфильтровать ссылки только на категории
            foreach ($urls as $url) {
                // добавить в очередь на скачивание
                $this->pool->insert($url);
            }
        }

        return [];
    }
}
