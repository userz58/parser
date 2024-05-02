<?php

namespace App\ValueObject;

interface ValueInterface
{
    public function getName(): string;

    public function getValue(): string;

    public function toArray(): array;

    //static public function fromArray(array $params): static;
}
