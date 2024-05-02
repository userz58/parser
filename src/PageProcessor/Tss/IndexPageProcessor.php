<?php

namespace App\PageProcessor\Tss;

use App\AsAttribute\AsProcessor;
use App\Event\IndexPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\TssParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [TssParser::CODE])]
class IndexPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    private const CHECK_TYPE_SELECTOR = 'body#index';

    public function isSupport(Crawler $crawler): bool
    {
        if (0 == $crawler->filter(self::CHECK_TYPE_SELECTOR)->count()) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return PageTypes::INDEX;
    }

    protected function postProcessed(Data $data): void
    {
        $this->eventDispatcher->dispatch(new IndexPageProcessedEvent($data));
    }
}
