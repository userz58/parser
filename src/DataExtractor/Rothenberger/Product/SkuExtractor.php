<?php

namespace App\DataExtractor\Rothenberger\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\PageTypes;
use App\Parser\RothenbergerParser;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class SkuExtractor implements ExtractorInterface
{
    private const BRAND_CODE  = 'rothenberger';
    protected string $label = 'Артикул';

    protected string $selector = '.detail .product-info .product-main .article__value';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $value = $crawler->filter($this->selector)->text();

        return [
            $this->label => $value,
            'Артикул производителя' => sprintf('%s-%s', self::BRAND_CODE, $value),
        ];
    }
}
