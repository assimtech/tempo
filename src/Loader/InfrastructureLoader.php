<?php

namespace Assimtech\Tempo\Loader;

use Symfony\Component\Yaml;
use Assimtech\Tempo\Factory\InfrastructureFactory;

class InfrastructureLoader extends AbstractLoader
{
    /**
     * @var \Assimtech\Tempo\Factory\InfrastructureFactory $factory
     */
    private $factory;

    /**
     * @var \Symfony\Component\Yaml\Parser $yamlParser
     */
    private $yamlParser;

    /**
     * @param \Assimtech\Tempo\Factory\InfrastructureFactory $factory
     * @param \Symfony\Component\Yaml\Parser $yamlParser
     */
    public function __construct(InfrastructureFactory $factory, Yaml\Parser $yamlParser)
    {
        $this->factory = $factory;
        $this->yamlParser = $yamlParser;
    }

    /**
     * @param string $path The path to a file defining \Assimtech\Tempo\Infrastructure
     *      defaults to 'tempo/infrastructure.yml'
     * @return \Assimtech\Tempo\Infrastructure
     */
    public function load($path)
    {
        $this->validatePath($path);
        $yaml = file_get_contents($path);
        $config = $this->yamlParser->parse($yaml);
        $infrastructure = $this->factory->create($config);

        return $infrastructure;
    }
}
