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
        $nodeFactory = new Factory\NodeFactory();
        $envFactory = new Factory\EnvironmentFactory();
        $infrastructureFactory = new Factory\InfrastructureFactory($nodeFactory, $envFactory);
        $yamlParser = new Yaml\Parser();
        $infrastructureLoader = new InfrastructureLoader($infrastructureFactory, $yamlParser);

        return $infrastructureLoader;
    }
}
