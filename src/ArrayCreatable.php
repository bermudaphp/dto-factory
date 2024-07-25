<?php

namespace Bermuda\Factory;

interface ArrayCreatable
{
    public static function fromArray(array $data): static ;
}
