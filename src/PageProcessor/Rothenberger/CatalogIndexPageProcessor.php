<?php

namespace App\PageProcessor\Rothenberger;

use App\AsAttribute\AsProcessor;
use App\Event\IndexPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\RothenbergerParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [RothenbergerParser::CODE])]
class CatalogIndexPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    private const CHECK_TYPE_SELECTOR = '.main-catalog-wrapper > .section-content-wrapper  > .catalog_section_list > .item_block';

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
