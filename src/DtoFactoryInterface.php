<?php

namespace Bermuda\Dto;

use Bermuda\Validation\ValidationException;

interface DtoFactoryInterface
{
  /**
    * @template T of DtoInterface
    * @param class-string<T> $dtoCls
    * @param array $data
    * @return DtoInterface
    * @throws \InvalidArgumentException
    * @throws DtoFactoryException
    * @throws ValidationException
    */
  public function make(string $dtoCls, array $data): DtoInterface ;

    /**
     * @template T of DtoInterface
     * @param class-string<T> $dtoCls
     * @return bool
     */
  public function canMake(string $dtoCls): bool ;
}
