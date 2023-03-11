<?php

namespace Bermuda\Dto;

interface DtoFactoryInterface
{
  /**
    * @template T of DtoInterface
    * @param class-string<T> $dtoCls
    * @param array $data
    * @return DtoInterface
    * @throws \InvalidArgumentException
    * @throws DtoFactoryException
    */
  public function make(string $dtoCls, array $data): DtoInterface ;
  public function canMake(string $dtoCls): bool ;
}
