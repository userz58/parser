<?php

namespace App\ValueObject;

class GridValue implements ValueInterface
{
    public function __construct(
        private string $name,
        private array  $values,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return implode('|', [implode(';', array_keys($this->values)), implode(';', $this->values)]);
    }

    public function toArray(): array
    {
        return [
            sprintf('%s (Названия)', $this->name) => implode(';', array_keys($this->values)),
            sprintf('%s (Значения)', $this->name) => implode(';', $this->values),
        ];
    }
}
