<?php

namespace Bermuda\Factory\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)] class Defaults
{
    public function __construct(
        public readonly mixed $value
    ) {
    }
}