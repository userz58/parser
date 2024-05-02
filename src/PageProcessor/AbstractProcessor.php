<?php

namespace App\PageProcessor;

use App\Event\ProductPageProcessedEvent;
use App\Manager\DataExtractorManager;
use App\Model\Data;
use App\Parser\PageTypes;
use App\Parser\ParserInterface;
use App\Parser\TssParser;
use App\Utils\WriterXlsx;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractProcessor
{
    protected array $extractors = [];

    protected ParserInterface $parser;

    public function __construct(
        protected DataExtractorManager     $extractorManager,
        protected EventDispatcherInterface $eventDispatcher,
    )
    {
    }

    abstract public function isSupport(Crawler $crawler): bool;

    abstract public function getType(): string;

    abstract protected function postProcessed(Data $data): void;

    public function process(string $url, Crawler $crawler): Data
    {
        $values = ['url' => $url];

        foreach ($this->extractors as $extractor) {
            $extracted = $extractor->extract($crawler);
            if (!empty($extracted)) {
                $values += $extracted;
            }
        }

        $data = Data::fromArray($values);

        $this->postProcessed($data);

        // todo: messenger
        // todo: скачивать картинки и файлы ?
        // todo: записывать результаты ?

        return $data;
    }

    public function getParser(): ParserInterface
    {
        return $this->parser;
    }

    public function setParser(ParserInterface $parser): void
    {
        $this->parser = $parser;

        $this->setExtractors();
    }

    private function setExtractors(): void
    {
        $parserCode = $this->getParser()->getCode();
        $extractorType = $this->getType();
        $this->extractors = $this->extractorManager->get($parserCode, $extractorType);
    }
}
