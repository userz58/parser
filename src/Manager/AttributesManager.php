<?php

namespace App\Manager;

class AttributesManager
{
    private array $productAttributes = [];
    private array $variantAttributes = [];

    public function getProductAttributes(): array
    {
        return $this->productAttributes;
    }

    public function addProductAttribute(string $attr): static
    {
        if (!in_array($attr, $this->productAttributes)) {
            $this->productAttributes[] = $attr;
        }

        return $this;
    }

    public function getVariantAttributes(): array
    {
        return $this->variantAttributes;
    }

    public function addVariantAttribute(string $attr): static
    {
        if (!in_array($attr, $this->variantAttributes)) {
            $this->variantAttributes[] = $attr;
        }

        return $this;
    }
}
