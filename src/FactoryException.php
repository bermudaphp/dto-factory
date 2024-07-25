<?php

namespace Bermuda\Factory;

class FactoryException extends \Exception
{
    public function __construct(
        string $msg,
        public readonly string $cls,
        public readonly array $data
    ) {
        parent::__construct($msg);
    }
}