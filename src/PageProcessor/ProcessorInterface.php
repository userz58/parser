<?php

namespace App\PageProcessor;

use App\Model\Data;
use App\Parser\ParserInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DomCrawler\Crawler;

#[AutoconfigureTag]
interface ProcessorInterface
{
    public function isSupport(Crawler $crawler): bool;

    public function process(string $url, Crawler $crawler): Data;

    public function getType(): string;

    public function setParser(ParserInterface $parser): void;

    public function getParser(): ParserInterface;
}
