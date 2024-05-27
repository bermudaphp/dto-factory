<?php

namespace Bermuda\Dto\Cast;

use Cycle\ORM\Reference\ReferenceInterface;

class ReferenceCaster implements CasterInterface
{
    public function __construct(
        private readonly string $referenceClass,
        private readonly string $referenceIdKey = 'id'
    ) {
    }

    public function cast(mixed $castable): ReferenceInterface
    {
        return new $this->referenceClass([$this->referenceIdKey => $castable]);
    }
}