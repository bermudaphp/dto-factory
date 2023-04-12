<?php

namespace Bermuda\Dto;

interface ArrayCreatable
{
    public static function fromArray(array $data): static ;
}
