<?php

namespace App\DataExtractor\Voll\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class TechExtractor implements ExtractorInterface
{
    protected string $label = 'Технические характеристики';

    protected string $selector = '.detail .tab-content #props .props_table tr';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            //throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
            // dump(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
            print_r(sprintf("Не найден элемент для селектора - %s [%s]\n", $this->label, $this->selector));

            return [];
        }

        $rows = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'label' => $node->filter('.char_name')->first()->text(),
                'value' => $node->filter('.char_value')->last()->text(),
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
