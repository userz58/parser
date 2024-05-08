<?php

namespace App\DataExtractor\Voll\ArticleIndex;

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
    supportedPageTypes: [PageTypes::ARTICLES],
    valueType: ValueTypes::LIST,
)]
class ArticlesExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Статьи';

    protected string $selector = '.blog .items .item';

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

        $list = $crawler->filter($this->selector)->each(function (Crawler $node, $i) {
            return [
                'uri' => $node->filter('.title a')->attr('href'),
                'Название' => $node->filter('.title a')->text(),
                'Изображение' => $node->filter('.image img')->attr('src'),
            ];
        });

        $formatted = [];
        foreach ($list as $value) {
            $url = sprintf('%s%s', self::BASE_HREF, $value['uri']);
            $hash = sha1($url);

            $formatted[$hash] = [
                'hash' => $hash,
                'url' => $url,
                'Название' => $this->formatter->format($value['Название']),
                'Изображение' => sprintf('%s%s', self::BASE_HREF, $value['Изображение']),
            ];

            $this->pool->add($url);
        }

        return [$this->label => $formatted];
    }
}
