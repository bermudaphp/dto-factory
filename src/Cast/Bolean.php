<?php

namespace Bermuda\Dto\Cast;

use Bermuda\Stdlib\StrHelper;

class Bolean implements CasterInterface
{
    public function cast(mixed $castable): ?bool
    {
        return StrHelper::toBool($castable);
    }
}