<?php

namespace Bermuda\Factory\Attribute;

use Bermuda\Factory\Cast\CasterInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)] class Cast implements \Bermuda\ContainerAwareInterface
{
    use \Bermuda\ContainerAwareTrait;

    /**
     * @var callable|class-string<T>
     */
    private $caster;
    private array $args = [];

    private static array $aliases = [];

    /**
     * @template T of CasterInterface
     * @param class-string<T>|string $caster
     */
    public function __construct(
        string|callable $caster,
        mixed ... $args
    ){
        $this->args = $args;
        $this->caster = $caster;
    }

    public function __invoke(mixed $prop): mixed
    {
        if (is_callable($this->caster)) {
            return ($this->caster)($prop, ... $this->args);
        }

        if (isset(self::$aliases[$this->caster])) {
            return self::$aliases[$this->caster]($prop);
        }

        $caster = new ($this->caster)(...$this->args);
        if ($caster instanceof \ContainerAwareInterface) {
            $caster->setContainer($this->container);
        }

        return $caster->cast($prop);
    }

    public static function setAlias(string $alias, callable $callback): void
    {
        self::$aliases[$alias] = $callback;
    }
}
