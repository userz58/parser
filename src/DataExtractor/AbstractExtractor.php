<?php

namespace App\DataExtractor;

use App\Parser\ParserInterface;

abstract class AbstractExtractor implements ExtractorInterface
{
    private ?ParserInterface $parser;

    private ?string $label;

    private bool $isRequired = false;

    public function getParser(): ?ParserInterface
    {
        return $this->parser;
    }

    public function setParser(?ParserInterface $parser): void
    {
        $this->parser = $parser;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }
}
