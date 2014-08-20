<?php

namespace Tempo;

use ArrayObject;
use InvalidArgumentException;
use OutOfBoundsException;

class Environment extends ArrayObject
{
    /** @var string $name */
    private $name;

    /** @var \Tempo\Node\AbstractNode[] $nodes */
    private $nodes;

    /** @var \Tempo\Node\AbstractNode[][] $roles */
    private $roles;

    /**
     * @var string $name Environment name, typically one of: development, staging, testing, demo, production
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->nodes = array();
        $this->roles = array();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param \Tempo\Node\AbstractNode $node
     * @param string|array $roles Optional for grouping of like nodes e.g. fep, web, db
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addNode(Node\AbstractNode $node, $roles = array())
    {
        if (is_string($roles)) {
            $roles = array($roles);
        } elseif (is_array($roles)) {
            foreach ($roles as $role) {
                if (!is_string($role)) {
                    throw InvalidArgumentException(sprintf(
                        'Environment: %s, roles must be a string or array of strings',
                        $this
                    ));
                }
            }
        } else {
            throw InvalidArgumentException(sprintf(
                'Environment: %s, roles must be a string or array of strings',
                $this
            ));
        }

        if (isset($this->nodes[(string)$node])) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s, Node: %s already exists',
                $this,
                $node
            ));
        }

        $this->nodes[(string)$node] = $node;
        foreach ($roles as $role) {
            if (!isset($this->roles[$role])) {
                $this->roles[$role] = array();
            }
            $this->roles[$role][] = $node;
        }

        return $this;
    }

    /**
     * @param string $name Name is optional if exactly one node is in the environment
     * @return \Tempo\Node\AbstractNode
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public function getNode($name = null)
    {
        if ($name === null) {
            if (count($this->nodes) !== 1) {
                $nodeNames = array();
                foreach ($this->nodes as $node) {
                    $nodeNames[] = (string)$node;
                }
                throw new InvalidArgumentException(sprintf(
                    'You must specify the node name because environment %s has more than 1 node: %s',
                    $this,
                    print_r($nodeNames, true)
                ));
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

    public function getNodes($role = null)
    {
        if ($role === null) {
            return $this->nodes;
        }

        if (!is_string($role)) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s, role must be a string or null for all nodes'
            ));
        }

        if (!isset($this->roles[$role])) {
            throw new OutOfBoundsException(sprintf(
                'Environment: %s, Role: %s doesn\'t exist',
                $this,
                $role
            ));
        }

        return $this->roles[$role];
    }
}
