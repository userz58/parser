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
class InstockExtractor implements ExtractorInterface
{
    protected string $label = '_availability';

    protected string $selector = '.detail .info .hh [itemprop="availability"]';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $value = $crawler->filter($this->selector)->attr('href');

        switch ($value) {
            case 'http://schema.org/InStock':
                $formatted = '1';
                break;
            case 'http://schema.org/PreOrder':
                $formatted = '0';
                break;
            default:
                $formatted = null;
        }

        return [$this->label => $formatted];
    }
}
