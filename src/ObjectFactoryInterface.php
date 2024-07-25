<?php

namespace Bermuda\Factory;

use Bermuda\Validation\ValidationException;

interface ObjectFactoryInterface
{
    /**
     * @template T of object
     * @param class-string<T> $cls
     * @return bool
     */
  public function canMake(string $cls): bool ;

    /**
     * @template T of object
     * @param class-string<T> $cls
     * @param array $data
     * @return T
     * @throws FactoryException
     * @throws ValidationException
     */
    public function make(string $cls, array $data): object ;

    /**
     * @template T of object
     * @param class-string<T> $cls
     * @param array $data
     * @return T
     * @throws FactoryException
     */
    public function makeWithoutValidation(string $cls, array $data): object ;
}
