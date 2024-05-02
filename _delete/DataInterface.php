<?php

namespace App\ValueObject;

interface DataInterface
{
    public function getPageName(): string;

    public function getValues(): array;

    public function setValues(array $values): self;

    public function addValue(ValueInterface $value): self;

    public function toArray(): array;
}
