<?php

namespace Bermuda\Dto;

final class DtoFactory implements DtoFactoryInterface
{
    private array $factories = [];
    private array $reflectors = [];
    
    /**
     * @param DtoFactoryInterface $factory
     * @return $this
     */
    public function addFactory(DtoFactoryInterface $factory): self
    {
        $this->factories[$factory::class] = $factory;
        return $this;
    }
    
    public function canMake(string $cls): bool
    {
        foreach($this->factories as $factory) {
            if ($factory->canMake($cls)) return true;
        }
        
        return $cls instanceof DtoInterface;
    }

    /**
     * @template T of DtoInterface
     * @param class-string<T> $cls
     * @param array $data
     * @return DtoInterface
     * @throws \InvalidArgumentException
     * @throws \DomainException
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
        $reflector = $this->getReflector($cls);
        $dto = $reflector->newInstanceWithoutConstructor();

        foreach ($reflector->getProperties() as $property) {
            if (array_key_exists($property->getName(), $data)) {
                if ($property->getAttributes(Without::class) != []) continue;
                if ($property->getType()->getName() instanceof DtoInterface) {
                    $property->setValue($dto, $this->make($property->getType()->getName(), $data[$property->getName()]));
                } else $property->setValue($dto, $data[$property->getName()]);
            } else {
                if (!$property->isInitialized($dto) && $property->hasDefaultValue()) $property->setValue($dto, $property->getDefaultValue());
                else if ($property->getType()->allowsNull()) $property->setValue($dto, null);
            }
        }

        return $dto;
    }
    
    /**
     * @param string $cls
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    private function getReflector(string $cls): \ReflectionClass
    {
        return $this->reflectors[$cls] ?? $this->reflectors[$cls] = new \ReflectionClass($cls);
    }
}
