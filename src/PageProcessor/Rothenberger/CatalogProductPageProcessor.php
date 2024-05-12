<?php

namespace App\PageProcessor\Rothenberger;

use App\AsAttribute\AsProcessor;
use App\Event\ProductPageBeforeProcessedEvent;
use App\Event\ProductPagePostProcessedEvent;
use App\Event\ProductPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\RothenbergerParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [RothenbergerParser::CODE])]
class CatalogProductPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    public const TYPE = PageTypes::PRODUCT;

    private const CHECK_TYPE_SELECTOR = '.product-container[itemtype="http://schema.org/Product"]';

    public function isSupport(Crawler $crawler): bool
    {
        if (0 == $crawler->filter(self::CHECK_TYPE_SELECTOR)->count()) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    protected function postProcessed(Data $data): void
    {
        $this->eventDispatcher->dispatch(new ProductPageBeforeProcessedEvent($data));

        $this->eventDispatcher->dispatch(new ProductPageProcessedEvent($data));

        $this->eventDispatcher->dispatch(new ProductPagePostProcessedEvent($data));
    }
}
