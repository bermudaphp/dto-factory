<?php

namespace Bermuda\Factory\Cast;

use Bermuda\ContainerAwareTrait;
use Cocur\Slugify\SlugifyInterface;
use Psr\Container\ContainerInterface;

final class Slugify implements CasterInterface, \ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param string $castable
     * @return string
     */
    public function cast(mixed $castable): string
    {
        static $slugger = null;
        if ($this->container && $this->container->has(SlugifyInterface::class)) {
            $slugger = $this->container->get(SlugifyInterface::class);
        } else $slugger = new \Cocur\Slugify\Slugify();
        
        return $slugger->slugify($castable);
    }
}
