<?php

namespace App\ValueObject;

class StringValue implements ValueInterface
{
    public function __construct(
        private string $name,
        private string $value,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [$this->name => $this->value];
    }
}
