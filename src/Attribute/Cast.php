<?php

namespace Bermuda\Dto\Attribute;

use Bermuda\Dto\Cast\CasterInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)] class Cast
{
    private array $args = [];

    /**
     * @template T of CasterInterface
     * @param class-string<T> $caster
     */
    public function __construct(
        private readonly string $caster,
        mixed ... $args
    ){
        $this->args = $args;
    }

    public function __invoke(mixed $prop): mixed
    {
        return (new $this->caster(...$this->args))->cast($prop);
    }
}
