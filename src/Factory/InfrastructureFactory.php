<?php

namespace Assimtech\Tempo\Factory;

use InvalidArgumentException;
use Assimtech\Tempo\Infrastructure;

/**
 * Constructs Infrastructure from configuration
 */
class InfrastructureFactory
{
    /**
     * @var \Assimtech\Tempo\Factory\NodeFactory $nodeFactory
     */
    private $nodeFactory;

    /**
     * @var \Assimtech\Tempo\Factory\EnvironmentFactory $envFactory
     */
    private $envFactory;

    /**
     * @param \Assimtech\Tempo\Factory\NodeFactory $nodeFactory
     * @param \Assimtech\Tempo\Factory\EnvironmentFactory $envFactory
     */
    public function __construct(NodeFactory $nodeFactory, EnvironmentFactory $envFactory)
    {
        $this->nodeFactory = $nodeFactory;
        $this->envFactory = $envFactory;
    }

    /**
     * @param array|null $config
     * @return \Assimtech\Tempo\Infrastructure
     * @throws \InvalidArgumentException
     */
    public function create(array $config)
    {
        if (!isset($config['nodes'])) {
            throw new InvalidArgumentException('config: [nodes] is mandatory');
        }

        if (!isset($config['environments'])) {
            throw new InvalidArgumentException('config: [environments] is mandatory');
        }

        $nodes = $this->nodeFactory->create($config['nodes']);
        $environments = $this->envFactory->create($config['environments'], $nodes);

        $infrastructure = new Infrastructure();
        $infrastructure->addEnvironments($environments);

        return $infrastructure;
    }
}
