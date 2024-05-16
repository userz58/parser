<?php

namespace App\PageProcessor\Voll;

use App\AsAttribute\AsProcessor;
use App\Event\ProductPageBeforeProcessedEvent;
use App\Event\ProductPagePostProcessedEvent;
use App\Event\ProductPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\VollParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [VollParser::CODE])]
class ProductPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    public const TYPE = PageTypes::PRODUCT;
    private const CHECK_TYPE_SELECTOR = '.section-content-wrapper >.detail[itemtype="http://schema.org/Product"]';

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
        $this->eventDispatcher->dispatch(new ProductPageBeforeProcessedEvent($data, $this->getParser()->getCode()));

        $this->eventDispatcher->dispatch(new ProductPageProcessedEvent($data, $this->getParser()->getCode()));

        $this->eventDispatcher->dispatch(new ProductPagePostProcessedEvent($data, $this->getParser()->getCode()));
    }
}
