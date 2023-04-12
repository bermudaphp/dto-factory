<?php

namespace Bermuda\Dto;

interface ArrayCreatable
{
    public function fromArray(array $data): DtoInterface ;
}
