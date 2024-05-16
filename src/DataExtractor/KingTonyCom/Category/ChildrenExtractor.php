<?php

namespace App\DataExtractor\KingTonyCom\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
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
class ChildrenExtractor implements ExtractorInterface
{
    protected string $label = '_children';

    public function __construct(
        private SluggerInterface $slugger,
        private Pool             $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        // если есть фильтр для подкатегорий
        if ($crawler->filter('.p_list li > a')->count() > 0) {
            return [];
        }

        // если нет фильтра .p_list, то можно скачивать подкатегории
        // .p_title > h3 > a
        $urls = $crawler->filter('.p_title > h3 > a')->each(fn(Crawler $node) => $node->attr('href'));
        if ([] == $urls) {
            return [];
        }

        $currentUrl = $crawler->getUri();
        $urls = array_filter($urls, fn($url) => $url !== $currentUrl);

        foreach ($urls as $url) {
            // добавить в очередь на скачивание
            $this->pool->insert($url);
        }

        return [];
    }
}
