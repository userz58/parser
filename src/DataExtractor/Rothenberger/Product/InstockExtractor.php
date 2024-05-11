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
class InstockExtractor implements ExtractorInterface
{
    protected string $label = '_availability';

    protected string $selector = '.detail .product-info .item-stock > .value';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $value = $crawler->filter($this->selector)->text();

        switch ($value) {
            case 'Под заказ':
                $formatted = '0';
                break;
            case 'Много':
            case 'Достаточно':
            case 'Мало':
                $formatted = '1';
                break;
            default:
                $formatted = null;
        }

        return [$this->label => $formatted];
    }
}
