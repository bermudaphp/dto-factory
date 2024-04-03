<?php

namespace Bermuda\Dto\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)] class From
{
    public function __construct(public readonly string $key)
    {
    }
}