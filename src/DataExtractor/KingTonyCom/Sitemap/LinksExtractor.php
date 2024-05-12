<?php

namespace App\DataExtractor\KingTonyCom\Sitemap;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

// https://www.kingtony.com/sitemap.xml

// страница категории
// https://www.kingtony.com/catalogs/VDE-Insulated-Tools/
// https://www.kingtony.com/catalogs2/2-VDE-Socket
// https://www.kingtony.com/catalogs3/6-Impact-Socket/6PT
// https://www.kingtony.com/catalogs4/Leisure-Products/Leisure-Products

// страница категории без фильтров
// https://www.kingtony.com/product_list.php?uID=1019

// страница товара
// https://www.kingtony.com/product/Travel-Bag-87732D
// https://www.kingtony.com/product_detail.php?uID=108&cID=533&Key=44
// https://www.kingtony.com/productlist/KINGTONY-Rock/16-PC-12-Point-Socket-Wrench-Set-6016MR

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::SITEMAP],
    valueType: ValueTypes::LIST,
)]
class LinksExtractor implements ExtractorInterface
{
    protected string $label = '_links';

    protected string $selector = '//default:loc';

    private array $skip = [
        'https://www.kingtony.com/product_search',
        'https://www.kingtony.com/e_learning',
        'https://www.kingtony.com/upload',
        'https://www.kingtony.com/e_catalog',
        'https://www.kingtony.com/members_',
        'https://www.kingtony.com/profile_',
        'https://www.kingtony.com/support_',
        'https://www.kingtony.com/download',
        'https://www.kingtony.com/news',
        //'',
    ];

    private array $allow = [
        'https://www.kingtony.com/product/',
        'https://www.kingtony.com/product_detail.php?',
        'https://www.kingtony.com/productlist/',
    ];

    public function __construct(
        private StringFormatter $formatter,
        private Pool            $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filterXPath($this->selector)->count() == 0) {
            //throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $urls = $crawler->filterXPath($this->selector)->each(fn(Crawler $node) => $node->text());

        $formatted = [];
        foreach ($urls as $url) {
            //$skip = false;
            //foreach ($this->skip as $template) {
            //    if (str_contains($url, $template)) {
            //        $skip = true;
            //        break;
            //    }
            //}

            $skip = true;
            foreach ($this->allow as $template) {
                if (str_contains($url, $template)) {
                    $skip = false;
                    break;
                }
            }

            if (false === $skip) {
                $formatted[sha1($url)] = $url;
                $this->pool->add($url);
            }
        }

        return [$this->label => $formatted];
    }
}
