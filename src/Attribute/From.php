<?php

namespace Bermuda\Factory\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)] class From
{
    public function __construct(
        public readonly string $key,
        public readonly bool $ifNull = false
    ) {
    }
}