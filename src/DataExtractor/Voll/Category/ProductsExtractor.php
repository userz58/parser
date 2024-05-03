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
            $sku = null;
            if ($node->filter('.article span')->count() > 0) {
                $sku = $node->filter('.article span')->text();
            }

            $price = null;
            $currency = null;
            if ($node->filter('.price_val[itemprop="price"]')->count() > 0) {
                $price = $node->filter('.price_val[itemprop="price"]')->attr('content');
                $currency = $node->filter('.currency[itemprop="priceCurrency"]')->attr('content');
            }

            $stickers = [];
            if ($node->filter('.stickers > .stickers-wrapper > div')->count() > 0) {
                $stickers = $node->filter('.stickers > .stickers-wrapper > div')->each(function (Crawler $stickerNode, $k) {
                    return $stickerNode->text();
                });
            }

            return [
                'uri' => $node->filter('.title a')->attr('href'),
                'Артикул' => $sku,
                'Название' => $node->filter('.title a')->text(),
                'Изображение' => $node->filter('img[itemprop="image"]')->attr('src'),
                'Цена' => $price,
                'Валюта' => $currency,
                'Хит' => $stickers,
                //availability
            ];
        });

        $formatted = [];
        foreach ($productsList as $value) {
            $url = sprintf('%s%s', self::BASE_HREF, $value['uri']);
            $hash = sha1($url);
            $img = sprintf('%s%s', self::BASE_HREF, $value['Изображение']);

            $formatted[$value['Артикул']] = [
                'hash' => $hash,
                'url' => $url,
                'Артикул' => $this->formatter->format($value['Артикул']),
                'Название' => $this->formatter->format($value['Название']),
                'Изображение' => $img,
                'Цена' => $value['Цена'],
                'Валюта' => $value['Валюта'],
                'Хит' => implode(';', $value['Хит']),
            ];

            $this->pool->add($url);
        }

        return [$this->label => $formatted];
    }
}
