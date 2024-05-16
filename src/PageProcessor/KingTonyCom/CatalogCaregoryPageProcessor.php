<?php

namespace App\PageProcessor\KingTonyCom;

use App\AsAttribute\AsProcessor;
use App\Event\CategoryPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\KingTonyComParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [KingTonyComParser::CODE])]
class CatalogCaregoryPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    public const TYPE = PageTypes::CATEGORY;

    public function isSupport(Crawler $crawler): bool
    {
        if (str_contains($crawler->getUri(), 'https://www.kingtony.com/catalogs')) {
            return true;
        }

        return false;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    protected function postProcessed(Data $data): void
    {
        //dump($data);
        //$parserCode = $this->getParser()->getCode();
        //die('category processed');
        // todo: ...
        $this->eventDispatcher->dispatch(new CategoryPageProcessedEvent($data, $this->getParser()->getCode()));
    }
}
