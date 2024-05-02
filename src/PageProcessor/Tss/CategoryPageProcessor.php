<?php

namespace App\PageProcessor\Tss;

use App\AsAttribute\AsProcessor;
use App\Event\CategoryPageProcessedEvent;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\TssParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [TssParser::CODE])]
class CategoryPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    private array $checkSelectors = [
        'body#catalog',
        'body#category',
        'body#portable-generators',

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
