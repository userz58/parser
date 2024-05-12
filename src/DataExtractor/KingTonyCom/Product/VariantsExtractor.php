<?php

namespace App\DataExtractor\KingTonyCom\Product;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [KingTonyComParser::CODE],
    supportedPageTypes: [PageTypes::PRODUCT],
    valueType: ValueTypes::LIST,
)]
class VariantsExtractor implements ExtractorInterface
{
    protected string $label = '_variants';

    protected string $selector = '#home-tab-pane table';
    private const VARIANT_NAME = 'Название';

    // http://www.unicode-symbol.com/
    // http://www.unicode-symbol.com/u/2B22.html
    private array $renameParams =
        [
            'Item' => 'Артикул производителя',
            'A05' => 'A, мм',
            'B01' => 'B, мм',
            'B02' => 'Размеры ДхШхВ, мм',
            'L01' => 'l, мм',
            'L02' => 'L, мм',
            'L03' => 'L1, мм',
            'L04' => 'L2, мм',
            'L05' => 'l, мм',
            'L06' => 'L, мм',
            'L07' => 'L, дюйм | мм',
            'L08' => 'L, мм',
            'L09' => 'Длина, мм',
            'L10' => 'L, мм | дюйм',
            'L11' => 'L, м',
            'L12' => 'Lw, мм',
            'L13' => 'L1, мм | дюйм',
            'L14' => 'L2, мм | дюйм',
            'L15' => 'L1, мм',
            'L16' => 'L, дюйм',
            'C01' => 'C, мм',
            'C02' => 'Carton',
            'C03' => 'Contents',
            'C04' => 'Contents',
            'C05' => 'Capacities, мм',
            'C06' => 'Clip type',
            'C07' => 'Категория',
            'C08' => 'Длина реза, мм',
            'C09' => 'Цвет рукоятки',
            'C10' => 'Copper (soft)',
            'C11' => 'Резка холодно-катанной стали',
            'C12' => 'Сutting capacities, mm',
            'C13' => 'Холодно-катанная сталь',
            'C14' => 'Медь',
            'C15' => 'Цвет рукоятки',
            'C16' => 'Длина реза, дюйм',
            'C17' => 'Длина цепи, дюйм',
            'C18' => 'Диапазон, мм',
            'C19' => 'Cu (медь)',
            'C20' => 'Диапазон, мм (для стали HRC30)',
            'C21' => 'Clip',
            'C22' => 'Cuft',
            'C23' => 'Capacities Screw size (inch | mm)',
            'C24' => 'Charger Suitable',
            'C25' => 'Cutting capacities (Медь|Сталь мягкая|Сталь HRC30|Нерж сталь 304)',
            'C26' => 'Cupper soft',
            'C27' => 'Резка стали (мягкой)',
            'C28' => 'Capacities (Hard steel HRC30)',
            'C29' => 'мм (Нерж сталь 304)',
            'C44' => 'Ролики',
            'S01' => 'S, мм',
            'S02' => 'Std Bold Size (inch|mm)',
            'S03' => 'Capacities Screw size (inch | mm)',
            'S04' => 'Хвостовик ⌀ х длина, мм',
            'S05' => 'Хвостовик ⌀ х длина, дюйм',
            'S06' => 'Квадрат приводной, дюйм',
            'S07' => 'Размер, мм',
            'S08' => 'Размер, №',
            'S09' => 'Размер, дюйм | мм',
            'S10' => 'Уровень звукового давления, DBA',
            'S11' => 'Квадрат',
            'S12' => '>< Мягкие материалы, мм',
            'S13' => 'Spec Cuft',
            'S14' => 'Slides type',
            'S15' => 'Нерж сталь 304',
            'S16' => 'Уровень шума, Дб',
            'S17' => 'Нерж сталь 304',
            'S18' => 'Состав',
            'S19' => 'Размер, мм',
            'S20' => 'Размер, дюйм',
            'S21' => 'Размер, мм | дюйм',
            'S22' => 'Диаметр, мм',
            'S23' => 'Screwdriver shell size, mm',
            'S24' => 'Страна',
            'S25' => 'Spread, mm|inch',
            'S33' => 'Вес, кг',
            'S37' => 'Поверхность',
            'D15' => 'Размеры ДхШхВ, мм',
            'N01' => 'Тип',
            'O07' => 'Общая длина, дюйм | мм',

            /*
            '001' => 'Размер, мм',
            '002' => 'Размер, дюйм',
            '003' => 'Размер, дюйм',
            '004' => 'Размер, мм',
            '005' => 'Размер, мм',
            '006' => 'Размер, мм',
            '007' => 'Размер, дюйм',
            '008' => 'Размер',
            '009' => 'Размер, мм',
            '010' => 'Размер, мм',
            '011' => 'Размер, дюйм',
            '012' => '№',
            '013' => 'Шестигранники, мм',
            '014' => 'Размер, мм',
            '015' => 'Квадрат, мм',
            '016' => 'Размер звездочки',
            //'017' => 'Размер звездочки',
            */
            '029' => 'Вес, г',
            '031' => 'Вес, кг',
            '025' => 'Кол-во в упаковке / Кол-во в коробке',
            '071' => 'Длина, мм|дюйм',
            '099' => 'Квадрат, мм'
        ];

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            return [];
        }

        //$models = $crawler->filter('.p_table > table tr:not(:first-child):not(.p_all) td label:not([for="checkall"])')->each(fn(Crawler $node) => trim($node->text()));
        //dump($models);

        $labels = $crawler->filter('.p_table > table > thead th')->each(function (Crawler $node, $i) {
            if ($node->filter('img')->count() > 0) {
                return $node->filter('img')->attr('src');
            } else {
                return trim($node->text());
            }
        });
        $labels = array_map(fn($i) => $this->formatLabels($i), $labels);
        //dump($labels);

        $variants = $crawler->filter('.p_table > table tr:not(:first-child):not(.p_all)')->each(function (Crawler $node, $i) {
            return $node->filter('td')->each(fn(Crawler $td) => trim($td->text()));
        });

        $formatted = [];
        foreach ($variants as $variant) {
            $values = [self::VARIANT_NAME => $variant[0]];
            foreach ($variant as $key => $param) {
                $values[$labels[$key]] = $param;
            }
            $formatted[$variant[0]] = $values;
        }

        return [$this->label => $formatted];
    }

    private function formatLabels(string $label): string
    {
        if (str_contains($label, 'https://www.kingtony.com/upload/products_title_img/')) {
            $label = str_replace(['https://www.kingtony.com/upload/products_title_img/', '.svg',], '', $label);
        }

        if (array_key_exists($label, $this->renameParams)) {
            $label = $this->renameParams[$label];
        }

        return $label;
    }
}
