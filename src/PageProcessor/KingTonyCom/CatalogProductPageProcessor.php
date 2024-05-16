<?php

namespace App\PageProcessor\KingTonyCom;

use App\AsAttribute\AsProcessor;
use App\Event\ProductPageBeforeProcessedEvent;
use App\Event\ProductPagePostProcessedEvent;
use App\Event\ProductPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\KingTonyComParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [KingTonyComParser::CODE])]
class CatalogProductPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    public const TYPE = PageTypes::PRODUCT;

    private const CHECK_TYPE_SELECTOR = '.p_d_box > h3';

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
