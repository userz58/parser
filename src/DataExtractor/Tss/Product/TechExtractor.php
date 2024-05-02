<?php

namespace App\DataExtractor\Tss\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\TssParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [TssParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class TechExtractor implements ExtractorInterface
{
    protected string $label = 'Технические характеристики';

    protected string $selector = '#product-features-tab-content table.table-data-sheet tr';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            //throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
            print_r(sprintf("Не найден элемент для селектора - %s [%s]\n", $this->label, $this->selector));

            return [];
        }

        $rows = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            $paramLabel = $node->filter('td')->first()->text();
            $paramValue = $node->filter('td')->last()->text();

            return [
                'label' => $paramLabel,
                'value' => $paramValue,
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
        $key = str_replace([
            'Д;Ш;В;',
            'dB',
        ], [
            'ДxШxВ,',
            'дБ',
        ], $key);

        $value = str_replace([
            'ШумозащитныйКожух',
        ], [
            'Шумозащитный кожух',
        ], $value);

        // заменить разделители в числах (запятые на точки)
        $value = preg_replace('/(?<=\d)\,(?=\d)/m', '.', $value);

        return [$key => $value];
    }
}
