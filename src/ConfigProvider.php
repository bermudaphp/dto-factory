<?php

namespace Bermuda\Dto;

use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
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
        return [DtoFactory::class => static function(ContainerInterface $c) {
            $config = conf($c);
            $factory = new DtoFactory($c->get(InvokerInterface::class));
            if (
                $config->offsetExists(self::configKey) 
                && $config->offsetExists(self::factoriesConfigKey)
            ) foreach ($config[self::configKey][self::factoriesConfigKey] as $dtoFactory) {
                $factory->addFactory($dtoFactory);
            }
            
            return $factory;
       }];
    }
}
