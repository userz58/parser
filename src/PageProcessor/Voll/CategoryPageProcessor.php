<?php

namespace App\PageProcessor\Voll;

use App\AsAttribute\AsProcessor;
use App\Event\CategoryPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\VollParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [VollParser::CODE])]
class CategoryPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    private array $checkSelectors = [
        '.table > .items[itemtype="http://schema.org/ItemList"] .item[itemtype="http://schema.org/Product"]',
        '.list > .items[itemtype="http://schema.org/ItemList"] .item[itemtype="http://schema.org/Product"]',
        '.catalog_page .section-content-wrapper > .table > .section > .catalog > div.items[itemtype="http://schema.org/ItemList"]',
    ];

    public function isSupport(Crawler $crawler): bool
    {
        foreach ($this->checkSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                return true;
            }
        }

        return false;
    }

    public function getType(): string
    {
        return PageTypes::CATEGORY;
    }

    protected function postProcessed(Data $data): void
    {
        $this->eventDispatcher->dispatch(new CategoryPageProcessedEvent($data));
    }
}
