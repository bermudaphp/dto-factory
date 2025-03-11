<?php

namespace Bermuda\Factory;

use Bermuda\ContainerAwareInterface;
use Bermuda\ContainerAwareTrait;
use Bermuda\Factory\Attribute\Defaults;
use Bermuda\Factory\Attribute\From;
use Bermuda\Factory\Attribute\Cast;
use Bermuda\Factory\Attribute\Invoke;
use Bermuda\Factory\Attribute\SkipProp;
use Bermuda\Factory\Cast\CasterInterface;
use Bermuda\Validation\ValidationException;
use Bermuda\Validation\ValidatorInterface;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;

final class ObjectFactory implements ContainerAwareInterface, ObjectFactoryInterface
{
    use ContainerAwareTrait;

    private array $factories = [];
    private array $reflectors = [];
    private array $validators = [];


    /**
     * @param DtoFactoryInterface $factory
     * @return $this
     */
    public function addFactory(ObjectFactoryInterface $factory): self
    {
        $this->factories[$factory::class] = $factory;

        if ($this->container && $factory instanceof ContainerAwareInterface) {
            $factory->setContainer($this->container);
        }

        return $this;
    }

    public function setValidator(string $cls, ValidatorInterface $validator): self
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
        if ($this->getFactory($cls)) return true;
        return class_exists($cls) && $this->getReflector($cls)
                ->isInstantiable();
    }

    /**
     * @template T of object
     * @param class-string<T> $cls
     * @param array $data
     * @return T
     * @throws FactoryException
     * @throws ValidationException
     */
    public function make(string $cls, array $data, bool $novalidate = false): object
    {
        $factory = $this->getFactory($cls);

        if (!$novalidate) {
            $this->getValidator($cls)?->validate($data);
        }
        
        return is_subclass_of($cls, ArrayCreatable::class) ? $cls::fromArray($data) :
            $this->makeFromReflection($cls, $data);
    }

    /**
     * @template T of object
     * @param class-string<T> $cls
     * @param string $json
     * @return T
     * @throws FactoryException
     * @throws ValidationException
     */
    public function makeFromJson(string $cls, string $json, bool $novalidate = false): object
    {
        return $this->make($cls, json_decode($json, true), $novalidate);
    }

    public function hasFactory(string $cls): bool
    {
        return isset($this->factories[$cls]);
    }

    public function getFactory(string $cls):? ObjectFactoryInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->canMake($cls)) return $factory;
        }

        return null;
    }

    private function makeFromReflection(string $cls, array $data): object
    {
        $reflector = $this->getReflector($cls);
        $obj = $reflector->newInstanceWithoutConstructor();

        foreach ($reflector->getProperties() as $property) {
            if ($property->getAttributes(Skip::class) != []) continue;
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
                $propValue = null;
                $cast = $property->getAttributes(Cast::class)[0] ?? null;
                if ($cast !== null) {
                    /**
                     * @var Cast $cast
                     */
                    ($cast = $cast->newInstance())
                        ->setContainer($this->container);
                    $propValue = $cast($data[$key]);
                }

                $property->setValue($obj, $propValue ?? $data[$key]);
            } else {
                $defaults = $property->getAttributes(Defaults::class)[0] ?? null;

                if (!$defaults) $defaults = $property->getAttributes(Invoke::class)[0] ?? null;
                if ($defaults) {
                    /**
                     * @var Defaults|Invoke $defaults
                     */
                    $defaults = $defaults->newInstance();
                    $property->setValue($obj, $defaults instanceof Invoke
                        ? $defaults->__invoke() : $defaults->value
                    );
                }
                else if (!$property->isInitialized($obj) && $property->hasDefaultValue()) $property->setValue($obj, $property->getDefaultValue());
                else if ($property->getType()->allowsNull()) $property->setValue($obj, null);
            }
        }

        return $obj;
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
