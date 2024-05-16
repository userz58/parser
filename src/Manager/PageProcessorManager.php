<?php

namespace App\Manager;

use App\PageProcessor\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class PageProcessorManager
{
    public function __construct(
        #[TaggedIterator(ProcessorInterface::class)]
        private iterable $processors,
    )
    {
    }

    public function get(string $parserCode): array
    {
        $filtered = [];
        foreach ($this->processors as $processor) {
            $reflection = new \ReflectionClass($processor::class);
            $classAttributes = $reflection->getAttributes();
            $instance = $classAttributes[0]->newInstance();

            if (in_array($parserCode, $instance->supportedParsers)) {
                $filtered[] = $processor;
            }
        }

        return $filtered;
    }
}
