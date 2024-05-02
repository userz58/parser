<?php

namespace App\AsAttribute;

use \Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AsProcessor
{
    public function __construct(
        public array  $supportedParsers,
    )
    {
    }
}
