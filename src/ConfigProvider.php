<?php

namespace Bermuda\Dto;

use Bermuda\Dto\Attribute\Cast;
use Psr\Container\ContainerInterface;
use function Bermuda\Config\callback;
use function Bermuda\Config\conf;

class ConfigProvider extends \Bermuda\Config\ConfigProvider
{
    const configKey = 'Bermuda\DtoFactory';
    const factoriesConfigKey = 'factories';

    protected function getAliases(): array
    {
        return [DtoFactoryInterface::class => DtoFactory::class];
    }

    protected function getFactories(): array
    {
        return [DtoFactory::class => static function(ContainerInterface $container) {
            $config = conf($container);
            $factory = new DtoFactory;
            $factory->setContainer($container);
            if (
                $config->offsetExists(self::configKey) 
                && $config->offsetExists(self::factoriesConfigKey)
            ) foreach ($config[self::configKey][self::factoriesConfigKey] as $dtoFactory) {
                $factory->addFactory($dtoFactory);
            }
            
            return $factory;
       }];
    }

    protected function getConfig(): array
    {
        return [
            self::bootstrap => callback(static function() {
                Cast::setAlias('json', static fn(string $encoded): array => json_decode($encoded, true));
            })
        ];
    }
}
