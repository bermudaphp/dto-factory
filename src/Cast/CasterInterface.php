<?php

namespace Bermuda\Factory\Cast;

interface CasterInterface
{
    public function cast(mixed $castable): mixed ;
}