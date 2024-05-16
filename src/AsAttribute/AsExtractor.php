<?php

namespace App\AsAttribute;

use \Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsExtractor
{
    public function __construct(
        public array  $supportedParsers,
        public array  $supportedPageTypes,
        public string $valueType,
        public bool   $isRequired = false,
    )
    {
    }
}
