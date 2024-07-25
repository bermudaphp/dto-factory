<?php

namespace Bermuda\Factory\Cast;

use Bermuda\Stdlib\StrHelper;

class Bolean implements CasterInterface
{
    public function cast(mixed $castable): ?bool
    {
        return StrHelper::toBool($castable);
    }
}