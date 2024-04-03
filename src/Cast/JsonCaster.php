<?php

namespace Bermuda\Dto\Cast;

class JsonCaster implements CasterInterface
{
    public function __construct(
        private readonly bool $associative = true,
        private readonly int $depth = 512,
        private readonly int $flags = 0
    ) {
    }

    public function cast(mixed $castable): array
    {
        return json_decode($castable, $this->associative, $this->depth, $this->flags);
    }
}