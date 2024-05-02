<?php

namespace App\DataExtractor\Voll\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::LIST,
)]
class ProductsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Товары';

    protected string $selector = '.catalog_page .items[itemtype="http://schema.org/ItemList"] .item';

    public function __construct(
        private StringFormatter $formatter,
        private Pool            $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() == 0) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $productsList = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            if ($node->filter('.article span')->count() > 0) {
                $sku = $node->filter('.article span')->text();
            } else {
                $sku = null;
            }

            return [
                'sku' => $sku,
                'uri' => $node->filter('.title a')->attr('href'),
                'name' => $node->filter('.title a')->text(),
                'image' => $node->filter('img[itemprop="image"]')->attr('src'),
                //'stickers' => $node->filter('.stickers > .stickers-wrapper > div')->text(),

                //stickers
                //price
                //currency
                //availability
            ];
        });

        $formatted = [];
        foreach ($productsList as $value) {
            $url = sprintf('%s%s', self::BASE_HREF, $value['uri']);
            $hash = sha1($url);
            $formatted[$value['sku']] = [
                'hash' => $hash,
                'url' => $url,
                'Артикул производителя' => $this->formatter->format($value['sku']),
                'Название' => $this->formatter->format($value['name']),
            ];

            $this->pool->add($url);
        }

        return [$this->label => $formatted];
    }
}
