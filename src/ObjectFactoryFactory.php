<?php

namespace Bermuda\Factory;

use Psr\Container\ContainerInterface;
use function Bermuda\Config\conf;

final class ObjectFactoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = conf($container);

        $factory = new ObjectFactory();
        $factory->setContainer($container);

        if (
            $config->offsetExists(ConfigProvider::configKey)
            && $config->offsetExists(ConfigProvider::factoriesConfigKey)
        ) foreach ($config[ConfigProvider::configKey][ConfigProvider::factoriesConfigKey] as $f) {
            $factory->addFactory($f);
        }

        return $factory;
    }
}