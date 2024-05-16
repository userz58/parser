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
class FilterCategoriesExtractor implements ExtractorInterface
{
    public function __construct(
        private Pool             $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        // если есть подкатегории в фильтрах - то скачивать только их
        if ((0 == $crawler->filter('.p_list li.active')->count()) && ($crawler->filter('.p_list li > a')->count() > 0)) {
            $urls = $crawler->filter('.p_list li > a')->each(fn(Crawler $node) => $node->attr('href'));
            foreach ($urls as $url) {
                // добавить в очередь на скачивание
                $this->pool->insert($url);
            }
        }

        return [];
    }
}
