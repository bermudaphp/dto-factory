<?php

namespace Bermuda\Dto\Cast;

interface CasterInterface
{
    public function cast(mixed $castable): mixed ;
}