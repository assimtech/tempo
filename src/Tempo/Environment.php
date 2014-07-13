<?php

namespace Tempo;

use InvalidArgumentException;
use OutOfBoundsException;

class Environment
{
    /** @var string $name Environment name, typically one of: development, staging, testing, demo, production */
    private $name;

    /** @var \Tempo\Node[] $nodes */
    private $nodes;

    /** @var callable[] $strategies */
    private $strategies;

    /**
     * @var string $name Environment name, typically one of: development, staging, testing, demo, production
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->nodes = array();
        $this->strategies = array();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
                'Environment: %s, Node: %s already exists',
                $this,
                $node
            ));
        }

        $this->nodes[(string)$node] = $node;

        return $this;
    }

    /**
     *
     * @param string $name Name is optional if exactly one node is in the environment
     * @return \Tempo\Node
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public function getNode($name = null)
    {
        if ($name === null) {
            if (count($this->nodes) !== 1) {
                throw new InvalidArgumentException(
                    'You must specify the node name'
                );
            }

            return current($this->nodes);
        }

        if (!isset($this->nodes[$name])) {
            throw new OutOfBoundsException(sprintf(
                'Environment: %s, Node: %s doesn\'t exist',
                $this,
                $name
            ));
        }

        return $this->nodes[$name];
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
                'Environment: %s, Strategy: %s already exists',
                $this,
                $name
            ));
        }

        $this->strategies[$name] = $strategy;

        return $this;
    }

    /**
     * @param string $name
     * @return \Tempo\Strategy[]
     */
    public function getStrategy($name)
    {
        if (!isset($this->strategies[$name])) {
            throw new OutOfBoundsException(sprintf(
                'Environment: %s, Strategy: %s doesn\'t exist',
                $this,
                $name
            ));
        }

        return $this->strategies[$name];
    }

    /**
     * @return \Tempo\Strategy[]
     */
    public function getStrategies()
    {
        return $this->strategies;
    }
}
