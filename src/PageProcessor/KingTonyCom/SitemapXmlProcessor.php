<?php

namespace App\PageProcessor\KingTonyCom;

use App\AsAttribute\AsProcessor;
use App\Event\SitemapXmlProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\KingTonyComParser;
use App\Parser\PageTypes;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [KingTonyComParser::CODE])]
class SitemapXmlProcessor extends AbstractProcessor implements ProcessorInterface
{
    public function isSupport(Crawler $crawler): bool
    {
        try {
            if (0 == $crawler->filterXPath('//default:urlset')->count()) {
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    public function getType(): string
    {
        return PageTypes::SITEMAP;
    }

    protected function postProcessed(Data $data): void
    {
        $this->eventDispatcher->dispatch(new SitemapXmlProcessedEvent($data));
    }
}
