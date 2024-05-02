<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\PageTypes;
use App\Parser\VollParser;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class BitrixIdExtractor implements ExtractorInterface
{
    protected string $label = '_inner_id_voll_ru';

    protected string $selector = '.detail > .item[data-id]';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $value = $crawler->filter($this->selector)->attr('data-id');

        return [$this->label => $value];
    }
}
