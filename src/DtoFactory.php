<?php

namespace Bermuda\Dto;

use Bermuda\Dto\Attribute\Defaults;
use Bermuda\Dto\Attribute\From;
use Bermuda\Dto\Attribute\Cast;
use Bermuda\Dto\Attribute\Invoke;
use Bermuda\Dto\Attribute\SkipProp;
use Bermuda\Dto\Cast\CasterInterface;
use Bermuda\Reflection\TypeMatcher;
use Bermuda\Validation\ValidationException;
use Bermuda\Validation\ValidatorInterface;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;

final class DtoFactory implements DtoFactoryInterface, \ContainerAwareInterface
{
    use \ContainerAwareTrait;

    private array $factories = [];
    private array $reflectors = [];
    private array $validators = [];

    /**
     * @param DtoFactoryInterface $factory
     * @return $this
     */
    public function addFactory(DtoFactoryInterface $factory): self
    {
        $this->factories[$factory::class] = $factory;
        return $this;
    }

    public function addValidator(string $cls, ValidatorInterface $validator): self
    {
        $this->validators[$cls] = $validator;
        return $this;
    }
    
    public function getValidator(string $cls):? ValidatorInterface
    {
        return $this->validators[$cls] ?? null ;
    }

    /**
     * @inheritdoc
     */
    public function canMake(string $cls): bool
    {
        foreach($this->factories as $factory) {
            if ($factory->canMake($cls)) return true;
        }
        
        return $cls instanceof DtoInterface;
    }

    public static function fromContainer(ContainerInterface $container): self
    {

    }

    /**
     * @template T of DtoInterface
     * @param class-string<T> $cls
     * @param array $data
     * @return DtoInterface
     * @throws \InvalidArgumentException
     * @throws \DomainException
     * @throws ValidationException
     */
    public function make(string $cls, array $data, bool $novalidate = false): DtoInterface
    {
        if (!is_subclass_of($cls, DtoInterface::class)) {
            throw new \InvalidArgumentException('Argument #1 ($cls) must be subclass of ' . DtoInterface::class);
        }

        if (!$novalidate) $this->getValidator($cls)?->validate($data);

        foreach ($this->factories as $factory) {
            if ($factory->canMake($cls)) return $factory->make($cls, $data);
        }

        return is_subclass_of($cls, ArrayCreatable::class) ? $cls::fromArray($data) :
            $this->makeFromReflection($cls, $data);
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
            if ($property->getAttributes(SkipProp::class) != []) continue;
            $from = $property->getAttributes(From::class)[0] ?? null;
            $key = null;
            if ($from) {
                /**
                 * @var From $from
                 */
                $from = $from->newInstance();
                if (($from->ifNull)) {
                    if (empty($data[$property->getName()])) $key = $from->key;
                } else {
                    $key = $from->key;
                }
            }

            $key = $key ?? $property->getName();
            if (array_key_exists($key, $data)) {
                if ($property->getType() instanceof \ReflectionIntersectionType
                    || $property->getType() instanceof \ReflectionUnionType) {
                    foreach ($property->getType()->getTypes() as $type) {
                        if ($type?->getName() instanceof DtoInterface) {
                            goto setDtoValue;
                        }
                    }
                    goto setValue;
                }
                else if ($property->getType()->getName() instanceof DtoInterface) {
                    setDtoValue:
                    $property->setValue($dto, $this->make($type?->getName() ?? $property->getType()->getName(), $data[$key]));
                } else {
                    setValue:
                    /**
                     * @var CasterInterface $cast
                     */
                    $cast = $property->getAttributes(Cast::class)[0] ?? null;
                    $propValue = null;

                    if ($cast) {
                        ($cast = $cast->newInstance())
                            ->setContainer($this->container);
                        $propValue = $cast($data[$key]);
                    }

                    $property->setValue($dto, $propValue ?? $data[$key]);
                }
            } else {
                $defaults = $property->getAttributes(Defaults::class)[0] ?? null;
                if (!$defaults) $defaults = $property->getAttributes(Invoke::class)[0] ?? null;
                if ($defaults) {
                    /**
                     * @var Defaults|Invoke $defaults
                     */
                    $defaults = $defaults->newInstance();
                    $property->setValue($dto, $defaults instanceof Invoke
                        ? $defaults->__invoke() : $defaults->value
                    );
                }
                else if (!$property->isInitialized($dto) && $property->hasDefaultValue()) $property->setValue($dto, $property->getDefaultValue());
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
