<?php

namespace Bermuda\DTO;

final class DtoFactory
{
    private array $factories = [];
    private array $reflectors = [];

    /**
     * @template T of DtoInterface
     * @param class-string<T> $cls
     * @param array $data
     * @return DtoInterface
     * @throws \InvalidArgumentException
     * @throws DomainException
     */
    public function make(string $cls, array $data): DtoInterface
    {
        if (!is_subclass_of($cls, DtoInterface::class)) {
            throw new \InvalidArgumentException('Argument #1 ($cls) must be subclass of ' . DtoInterface::class);
        }

        foreach ($this->factories as $factory) {
            if ($factory->canMake($cls)) return $factory->make($cls, $data);
        }
        
        return $this->makeFromReflection($cls, $data);
    }

    public function hasFactory(string $cls): bool
    {
        return isset($this->factories[$cls]);
    }

    private function makeFromReflection(string $cls, array $data): DtoInterface
    {
        if (!isset($this->reflectors[$cls])) {
            $this->reflectors[$cls] = new \ReflectionClass($cls);
        }

        $reflector = $this->reflectors[$cls];

        $dto = $reflector->newInstanceWithoutConstructor();

        $props = $reflector->getProperties();

        foreach ($props as $property) {
            if (array_key_exists($property->getName(), $data)) {
                if ($property->getAttributes(Without::class) != []) continue;
                if ($property->getType()->getName() instanceof DtoInterface) {
                    $property->setValue($dto, $this->make($property->getType()->getName(), $data[$property->getName()]));
                } else {
                    $property->setValue($dto, $data[$property->getName()]);
                }
            } else {
                if (!$property->isInitialized($dto) && $property->hasDefaultValue()) {
                    $property->setValue($dto, $property->getDefaultValue());
                } else if ($property->getType()->allowsNull()) {
                     $property->setValue($dto, null);
                }
            }
        }

        return $dto;
    }
}
