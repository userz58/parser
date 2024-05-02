<?php

namespace App\DataExtractor\Voll\Index;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Formatter\StringFormatter;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::INDEX],
    valueType: ValueTypes::LIST,
)]
class IndexCategoriesExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Разделы';

    protected string $selector = '.section-content-wrapper > .catalog > .items .item > .info a';

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
                //'label' => $node->attr('title'),
                'uri' => $node->attr('href'),
                //'img' => $node->filter('img')->attr('src'),
            ];
        });

        $formatted = [];
        foreach ($values as $value) {
            $url = sprintf('%s%s', self::BASE_HREF, $value['uri']);

            $formatted[sha1($url)] = [
                'hash' => sha1($url),
                'url' => $url,
                'Название' => $this->formatter->format($value['label']),
                //'Изображение' => sprintf('%s%s', self::BASE_HREF, $value['img']),
                //'Раздел-родитель' => '',
            ];

            $this->pool->add($url);
        }

        return [
            $this->label => $formatted
        ];
    }
}
