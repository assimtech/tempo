<?php

namespace Tempo;

use InvalidArgumentException;
use OutOfBoundsException;

class Tempo
{
    /** @var \Tempo\Environment[] $environments */
    private $environments;

    /** @var \Tempo\Node[] $nodes */
    private $nodes;

    /** @var callable[] $strategies */
    private $strategies;

    public function __construct()
    {
        $this->environments = array();
        $this->nodes = array();
        $this->strategies = array();
    }

    /**
     * @param \Tempo\Environment $environment
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addEnvironment(Environment $environment)
    {
        if (isset($this->environments[(string)$environment])) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s already exists',
                $environment
            ));
        }

        $this->environments[(string)$environment] = $environment;

        return $this;
    }

    /**
     * @param string $name
     * @return \Tempo\Environment
     */
    public function getEnvironment($name)
    {
        if (!isset($this->environments[$name])) {
            throw new OutOfBoundsException(sprintf(
                'Environment: %s doesn\'t exist',
                $name
            ));
        }

        return $this->environments[$name];
    }

    /**
     * @param \Tempo\Node $node
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addNode(Node $node)
    {
        if (isset($this->nodes[(string)$node])) {
            throw new InvalidArgumentException(sprintf(
                'Node: %s already exists',
                $node
            ));
        }

        $this->nodes[(string)$node] = $node;

        return $this;
    }

    /**
     * @param string $name
     * @return \Tempo\Node
     */
    public function getNode($name)
    {
        if (!isset($this->node[$name])) {
            throw new OutOfBoundsException(sprintf(
                'Node: %s doesn\'t exist',
                $name
            ));
        }

        return $this->node[$name];
    }

    /**
     * @param string $name
     * @param callable $strategy
     * @return self
     */
    public function addStrategy($name, $strategy)
    {
        if (isset($this->strategies[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Strategy: %s already exists',
                $name
            ));
        }

        $this->strategies[$name] = $strategy;

        return $this;
    }

    /**
     * @return \Tempo\Strategy
     */
    public function getStrategy($name)
    {
        return $this->strategies[$name];
    }
}
