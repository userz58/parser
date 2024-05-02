<?php

namespace App\DataExtractor\Voll\Category;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\VollParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [VollParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY],
    valueType: ValueTypes::LIST,
)]
class NextPageExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://voll.ru';

    protected string $label = 'Следующая страница в пагинаторе';

    protected string $selector = '.pagination .next a';

    public function __construct(
        private Pool $pool,
    )
    {
    }

    public function extract(Crawler $crawler): array
    {
        if ($crawler->filter($this->selector)->count() > 0) {
            $value = $crawler->filter($this->selector)->attr('href');
            $url = sprintf('%s%s', self::BASE_HREF, $value);

            $this->pool->insert($url);
        }

        return [];
    }
}
