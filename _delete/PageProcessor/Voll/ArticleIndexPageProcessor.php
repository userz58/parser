<?php

namespace App\PageProcessor\Voll;

use App\AsAttribute\AsProcessor;
use App\Event\ArticleIndexPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\VollParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [VollParser::CODE])]
class ArticleIndexPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    private const CHECK_TYPE_SELECTOR = 'h1#pagetitle';
    private const VALUE = 'Статьи';

    public function isSupport(Crawler $crawler): bool
    {
        if (0 == $crawler->filter(self::CHECK_TYPE_SELECTOR)->count()) {
            return false;
        }


        if (self::VALUE !== $crawler->filter(self::CHECK_TYPE_SELECTOR)->text()) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return PageTypes::ARTICLES;
    }

    protected function postProcessed(Data $data): void
    {
        $this->eventDispatcher->dispatch(new ArticleIndexPageProcessedEvent($data));
    }
}
