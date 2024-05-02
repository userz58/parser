<?php

namespace App\Manager;

use App\DataExtractor\ExtractorInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class DataExtractorManager
{
    public function __construct(
        #[TaggedIterator(ExtractorInterface::class)]
        private iterable $extractors,
    )
    {
    }

    public function get(string $parserCode, string $pageType): array
    {
        $filtered = [];
        foreach ($this->extractors as $extractor) {
            $reflection = new \ReflectionClass($extractor::class);
            $classAttributes = $reflection->getAttributes();
            $instance = $classAttributes[0]->newInstance();

            if (in_array($parserCode, $instance->supportedParsers) && in_array($pageType, $instance->supportedPageTypes)) {
                $filtered[] = $extractor;
            }
        }

        return $filtered;
    }
}
