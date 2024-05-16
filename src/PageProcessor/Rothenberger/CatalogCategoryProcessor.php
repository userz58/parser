<?php

namespace App\PageProcessor\Rothenberger;

use App\AsAttribute\AsProcessor;
use App\Event\CategoryPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\RothenbergerParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [RothenbergerParser::CODE])]
class CatalogCategoryProcessor extends AbstractProcessor implements ProcessorInterface
{
    private array $checkSelectors = [
        '#right_block_ajax > .inner_wrapper > .ajax_load > .js_wrapper_items .items_wrapper > .catalog_block > .item_block',  // блоки
        '#right_block_ajax > .inner_wrapper > .ajax_load > .display_list > .list_item_wrapp > .list_item', // список
        '#right_block_ajax > .inner_wrapper > .ajax_load > .table-view > .table-view__item[data-id]', // таблица
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
