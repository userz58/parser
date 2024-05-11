<?php

namespace App\DataExtractor\Rothenberger\Article;

use App\AsAttribute\AsExtractor;
use App\DataExtractor\ExtractorInterface;
use App\Parser\RothenbergerParser;
use App\Parser\PageTypes;
use App\Parser\ValueTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsExtractor(
    supportedParsers: [RothenbergerParser::CODE],
    supportedPageTypes: [PageTypes::ARTICLE],
    valueType: ValueTypes::STRING,
)]
class DetailImageExtractor implements ExtractorInterface
{
    const BASE_HREF = 'https://rothenberger.ru';

    protected string $label = 'Изображение';

    protected string $selector = '.news .detail_wrapper .detailimage a';

    public function extract(Crawler $crawler): array
    {
        if (0 == $crawler->filter($this->selector)->count()) {
            throw new \RuntimeException(sprintf('Не найден элемент для селектора - %s [%s]', $this->label, $this->selector));
        }

        $value = $crawler->filter($this->selector)->attr('href');

        if (empty($value)) {
            return [$this->label => null];
        }

        $formatted = sprintf('%s%s', self::BASE_HREF, $value);

        return [$this->label => $formatted];
    }
}
