<?php

namespace App\DataExtractor\Rothenberger\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::LIST,
)]
class ProductsTableExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = 'Товары';

    protected string $selector = '#right_block_ajax > .inner_wrapper > .ajax_load > .table-view > .table-view__item[data-id]';

    public function __construct(
        private StringFormatter $formatter,
        private Pool            $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() == 0) {
            return [];
        }

        $productsList = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            $price = null;
            $currency = null;
            if ($node->filter('.price_matrix_wrapper > .price[data-currency]')->count() > 0) {
                $price = $node->filter('.price_matrix_wrapper > .price')->attr('data-value');
                $currency = $node->filter('.price_matrix_wrapper > .price')->attr('data-currency');
            }

            return [
                'uri' => $node->filter('.item-title > a')->attr('href'),
                'Название' => $node->filter('.item-title > a')->text(),
                'Цена' => $price,
                'Валюта' => $currency,
                '_inner_bitrix_id' => $node->attr('data-id'),
                '_availability' => $node->filter('.item-stock > .value')->text(),
            ];
        });

        $formatted = [];
        foreach ($productsList as $value) {
            $url = sprintf('%s%s', self::BASE_HREF, $value['uri']);

            $formatted[$value['Артикул']] = [
                'hash' => sha1($url),
                'url' => $url,
                'Название' => $this->formatter->format($value['Название']),
                'Цена' => $value['Цена'],
                'Валюта' => $value['Валюта'],
                'Наши предложения' => null,
                'Артикул' => null,
                '_inner_bitrix_id' => $this->formatter->format($value['Артикул']),
                '_availability' => $value['Артикул'] == 'Под заказ' ? 0 : 1,
                'Изображение' => null,
            ];

            $this->pool->add($url);
        }

        return [$this->label => $formatted];
    }
}
