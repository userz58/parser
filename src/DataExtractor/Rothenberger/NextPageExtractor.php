<?php

namespace App\DataExtractor\Rothenberger;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use App\Parser\RothenbergerParser;
use App\Pool\Pool;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::CATEGORY, PageTypes::ARTICLES],
    valueType: ValueTypes::LIST,
)]
class NextPageExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = 'Следующая страница в пагинаторе';

    protected string $selector = '.module-pagination a.flex-next';

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
