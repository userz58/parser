<?php

namespace App\PageProcessor\Rothenberger;

use App\AsAttribute\AsProcessor;
use App\Event\ArticlePageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\RothenbergerParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [RothenbergerParser::CODE])]
class ArticlePageProcessor extends AbstractProcessor implements ProcessorInterface
{
    private const CHECK_TYPE_SELECTOR = '#navigation #bx_breadcrumb_1 a';
    private const VALUE = 'Статьи';

    public function isSupport(Crawler $crawler): bool
    {
        if (0 == $crawler->filter(self::CHECK_TYPE_SELECTOR)->count()) {
            return false;
        }


        if (self::VALUE !== $crawler->filter(self::CHECK_TYPE_SELECTOR)->attr('title')) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return PageTypes::ARTICLE;
    }

    protected function postProcessed(Data $data): void
    {
        $this->eventDispatcher->dispatch(new ArticlePageProcessedEvent($data));
    }
}
