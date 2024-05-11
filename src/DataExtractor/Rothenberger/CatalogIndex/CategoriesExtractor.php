<?php

namespace App\DataExtractor\Rothenberger\CatalogIndex;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::INDEX],
    valueType: ValueTypes::LIST,
)]
class CategoriesExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = 'Разделы';

    protected string $selector = '.catalog_section_list .item_block .name > a';

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

        $values = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'label' => $node->text(),
                'uri' => $node->attr('href'),
            ];
        });

        $formatted = [];
        foreach ($values as $value) {
            $url = sprintf('%s%s', self::BASE_HREF, $value['uri']);

            $formatted[sha1($url)] = [
                'hash' => sha1($url),
                'url' => $url,
                'Название' => $this->formatter->format($value['label']),
            ];

            $this->pool->add($url);
        }

        return [$this->label => $formatted];
    }
}
