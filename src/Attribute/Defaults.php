<?php

namespace Bermuda\Dto\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)] class Defaults
{
    public function __construct(
        public readonly mixed $value
    ) {
    }
}