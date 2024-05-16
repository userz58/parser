<?php

namespace App\PageProcessor\KingTonyCom;

use App\AsAttribute\AsProcessor;
use App\Model\Data;
use App\PageProcessor\AbstractProcessor;
use App\PageProcessor\ProcessorInterface;
use App\Parser\PageTypes;
use App\Parser\KingTonyComParser;
use Symfony\Component\DomCrawler\Crawler;

#[AsProcessor(supportedParsers: [KingTonyComParser::CODE])]
class CategoryIndexPageProcessor extends AbstractProcessor implements ProcessorInterface
{
    public const TYPE = PageTypes::INDEX;

    public function isSupport(Crawler $crawler): bool
    {
        if (str_contains($crawler->getUri(), 'https://www.kingtony.com/product.php')) {
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
        // todo: ...
    }
}
