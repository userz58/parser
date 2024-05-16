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
class ProductsExtractor implements ExtractorInterface
{
    protected string $label = '_products';

    public function __construct(
        private SluggerInterface $slugger,
        private Pool             $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
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

        // товары
        $products = $crawler->filter('.p_slide > a')->each(function (Crawler $node, $i) {
            if ($node->filter('h3 p')->count() > 0) {
                $title = trim($node->filter('h3')->text());
                $summary = trim($node->filter('h3 p')->last()->text());
                $name = trim(str_replace($summary, '', $title));
            } else {
                $name = trim($node->filter('h3')->text());
            }

            return [
                'Название' => $name,
                'url' => $node->attr('href'),
            ];
        });


        // https://www.kingtony.com/productlist/KINGTONY-Rock/25-PC-6-Point-Socket-Wrench-Set-2525MRE
        $products = array_filter($products, fn($p) => !str_contains($p['url'], 'https://www.kingtony.com/catalogs'));

        $formatted = [];
        foreach ($products as $product) {
            // отфильтровать категории
            //if (str_contains($product['url'], 'https://www.kingtony.com/catalogs')) {
            //    dump($product['url']);
            //    $this->pool->insert($product['url']);
            //    continue;
            //}

            // добавить в очередь на скачивание
            $formatted[] = $product['Название'];
        }

        return [$this->label => $formatted];
    }
}
