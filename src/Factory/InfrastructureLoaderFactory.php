<?php

namespace Assimtech\Tempo\Factory;

use Assimtech\Tempo\Loader\InfrastructureLoader;
use Assimtech\Tempo\Factory;
use Symfony\Component\Yaml;

/**
 * Constructs an InfrastructureLoader
 */
class InfrastructureLoaderFactory
{
    /**
     * @return \Assimtech\Tempo\Loader\InfrastructureLoader
     */
    public static function create()
    {
        return new InfrastructureLoader(
            new Factory\InfrastructureFactory(
                new Factory\NodeFactory(),
                new Factory\EnvironmentFactory()
            ),
            new Yaml\Parser()
        );
    }
}
