<?php

namespace App\DataExtractor\Tss\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\TssParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [TssParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::LIST,
)]
class ProductsExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://tss.ru';

    protected string $label = 'Товары';

    protected string $selector = '.product_list.grid .ajax_block_product';

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

        $productsList = $crawler->filter('.product_list.grid .ajax_block_product ')->each(function (Crawler $node, $i) {
            return [
                'uri' => $node->filter('a.product-name[itemprop="url"]')->attr('href'),
                'sku' => $node->filter('.hook-reviews .comments_note span')->text(),
                'name' => $node->filter('a.product-name[itemprop="url"]')->text(),
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
