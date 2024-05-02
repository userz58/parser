<?php

namespace App\ValueObject;

abstract class AbstractData implements DataInterface
{
    private array $values = [];

    abstract public function getPageName(): string;

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): self
    {
        $this->values = $values;

        return $this;
    }

    public function addValue(ValueInterface $value): self
    {
        $this->values[$value->getName()] = $value;

        return $this;
    }

    public function toArray(): array
    {
        return array_map(fn($item) => $item->toArray(), $this->values);
    }
}
