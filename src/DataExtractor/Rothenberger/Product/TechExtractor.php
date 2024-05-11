<?php

namespace App\DataExtractor\Rothenberger\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class TechExtractor implements ExtractorInterface
{
    protected string $label = 'Технические характеристики';

    protected string $selector = '.detail .tab-content #props table tr';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            //throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
            // dump(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
            print_r(sprintf("Не найден элемент для селектора - %s [%s]\n\n", $this->label, $this->selector));

            return [];
        }

        $rows = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'label' => $node->filter('.char_name [itemprop="name"]')->text(),
                'value' => $node->filter('.char_value [itemprop="value"]')->text(),
            ];
        });

        $formatted = [];
        foreach ($rows as $row) {
            if (!empty($row['label'])) {
                $formatted += $this->formatValue($row['label'], $row['value']);
            }
        }

        return $formatted;
    }

    // отформатировать значения
    private function formatValue(string $key, string $value): array
    {
        $key = trim($key);
        $value = trim($value);

        return [$key => $value];
    }
}
