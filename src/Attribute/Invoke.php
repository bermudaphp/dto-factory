<?php

namespace Bermuda\Dto\Attribute;

use Bermuda\Clock\Clock;

/**
 * @property callable $value
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)] class Invoke extends Defaults
{
    private readonly array $args;
    private static array $aliases = [
        'now' => [Clock::class, 'now'],
        'timestamp' => [Clock::class, 'timestamp'],
    ];

    public function __construct(string|callable $value, mixed ... $args)
    {
        if (!is_callable($value)) {
            $value = static::$aliases[$value] ?? null;
            if (!$value) {
                throw \InvalidArgumentException('Argument #1 ($value) must be of type callable or string registered as alias through Invoke::setAlias()');
            }
        }
        $this->args = $args;
        parent::__construct($value);
    }

    public function __invoke()
    {
        return ($this->value)(...$this->args);
    }

    public static function setAlias(string $alias, callable $callback): void
    {
        static::$aliases[$alias] = $callback;
    }
}