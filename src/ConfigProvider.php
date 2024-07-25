<?php

namespace Bermuda\Factory;

use Bermuda\Factory\Attribute\Cast;
use Psr\Container\ContainerInterface;
use function Bermuda\Config\callback;
use function Bermuda\Config\conf;

class ConfigProvider extends \Bermuda\Config\ConfigProvider
{
    const configKey = 'Bermuda\Factory';
    const factoriesConfigKey = 'FACTORIES';

    protected function getFactories(): array
    {
        return [ObjectFactory::class => ObjectFactoryFactory::class];
    }

    protected function getConfig(): array
    {
        return [
            self::bootstrap => callback(static function() {
                Cast::setAlias('json', static fn(string $encoded): array => json_decode($encoded, true, flags: JSON_THROW_ON_ERROR));
            })
        ];
    }
}
