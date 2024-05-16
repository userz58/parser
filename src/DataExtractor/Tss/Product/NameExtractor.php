<?php

namespace App\DataExtractor\Tss\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\TssParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [TssParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::STRING,
)]
class NameExtractor implements ExtractorInterface
{
    protected string $label = 'Наименование';

    protected string $selector = '#center_column h1[itemprop=name]';

    public function __construct(
        private StringFormatter $formatter,
    )
    {
    }
    public function extract(Crawler $crawler): array
    {
        // todo: оставить проверку в каждом классе или перенести в try ???
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $value = $crawler->filter($this->selector)->text();
        $value = $this->formatter->format($value);

        return [$this->label => $value];
    }
}
