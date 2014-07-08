<?php

namespace Tempo;

class Environment
{
    /** @var string $name Environment name, typically one of: development, staging, testing, demo, production */
    private $name;

    /** @var \Tempo\Node[] $nodes */
    private $nodes;

    /**
     * @var string $name Environment name, typically one of: development, staging, testing, demo, production
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->nodes = array();
    }

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
}
