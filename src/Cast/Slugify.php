<?php

namespace Bermuda\Dto\Cast;

use Psr\Container\ContainerInterface;
use Slugger\Slugger;
use Slugger\SluggerInterface;

final class Slugify implements CasterInterface, \ContainerAwareInterface
{
    use \ContainerAwareTrait;

    /**
     * @param string $castable
     * @return string
     */
    public function cast(mixed $castable): string
    {
        $slugger = $this->container->get(SluggerInterface::class);
        return $slugger->slugify($castable);
    }
}