<?php

namespace Assimtech\Tempo;

use ArrayObject;
use InvalidArgumentException;
use OutOfBoundsException;

class Environment extends ArrayObject
{
    /**
     * @var array|string $properties Environment name, typically one of: development, staging, testing, demo, production
     */
    public function __construct($properties)
    {
        // Handle string shortcut setup
        if (is_string($properties)) {
            $properties = array(
                'name' => $properties,
            );
        }

        if (!is_array($properties)) {
            throw new InvalidArgumentException('properties must be either an array or string');
        }

        if (!isset($properties['nodes'])) {
            $properties['nodes'] = array();
        }

        if (!isset($properties['roles'])) {
            $properties['roles'] = array();
        }

        self::validateProperties($properties);

        parent::__construct($properties);
    }

    /**
     * @param array $properties
     * @throws \InvalidArgumentException
     */
    protected static function validateProperties(array $properties)
    {
        if (!isset($properties['name']) || empty($properties['name'])) {
            throw new InvalidArgumentException('property: [name] is mandatory');
        }

        $foundNodes = array();
        foreach ($properties['nodes'] as $node) {
            if (!$node instanceof Node\AbstractNode) {
                throw new InvalidArgumentException(
                    'property: [nodes][] must be instances of Assimtech\Tempo\Node\AbstractNode'
                );
            }

            if (in_array((string)$node, $foundNodes)) {
                throw new InvalidArgumentException(sprintf(
                    'property: [nodes][] contains duplicate node: %s',
                    (string)$node
                ));
            }

            $foundNodes[] = (string)$node;
        }

        foreach ($properties['roles'] as $role => $nodes) {
            foreach ($nodes as $node) {
                if (!in_array($node, $properties['nodes'])) {
                    throw new InvalidArgumentException(sprintf(
                        'property: [roles][%s][%s] is not a member of [nodes][]',
                        $role,
                        $node
                    ));
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this['name'];
    }

    /**
     * @param \Assimtech\Tempo\Node\AbstractNode $node
     * @param string|array $roles Optional for grouping of like nodes e.g. fep, web, db
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addNode(Node\AbstractNode $node, $roles = array())
    {
        if (is_string($roles)) {
            $roles = array(
                $roles,
            );
        }

        if (!is_array($roles)) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s, roles must be a string or array of strings',
                $this
            ));
        }

        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException(sprintf(
                    'Environment: %s, roles must be a string or array of strings',
                    $this
                ));
            }
        }

        $knownNodeNames = array();
        foreach ($this['nodes'] as $knownNode) {
            $knownNodeNames[] = (string)$knownNode;
        }
        if (in_array((string)$node, $knownNodeNames)) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s, Node: %s already exists',
                $this,
                $node
            ));
        }

        $this['nodes'][] = $node;

        foreach ($roles as $role) {
            if (!isset($this['roles'][$role])) {
                $this['roles'][$role] = array();
            }
            $this['roles'][$role][] = $node;
        }

        return $this;
    }

    /**
     * @param \Assimtech\Tempo\Node\AbstractNode[] $nodes
     * @param string|array $roles Optional for grouping of like nodes e.g. fep, web, db
     * @return self
     */
    public function addNodes($nodes, $roles = array())
    {
        foreach ($nodes as $node) {
            $this->addNode($node, $roles);
        }

        return $this;
    }

    /**
     * @param string $name Name is optional if exactly one node is in the environment
     * @return \Assimtech\Tempo\Node\AbstractNode
     * @throws \InvalidArgumentException
     * @throws \OutOfBoundsException
     */
    public function getNode($name = null)
    {
        if ($name === null) {
            if (count($this['nodes']) !== 1) {
                $nodeNames = array();
                foreach ($this['nodes'] as $node) {
                    $nodeNames[] = (string)$node;
                }
                throw new InvalidArgumentException(sprintf(
                    'You must specify the node name because environment %s has more than 1 node: %s',
                    $this,
                    implode(', ', $nodeNames)
                ));
            }

            return $this['nodes'][0];
        }

        foreach ($this['nodes'] as $node) {
            if (((string)$node) === $name) {
                return $node;
            }
        }

        throw new OutOfBoundsException(sprintf(
            'Environment: %s, Node: %s doesn\'t exist',
            $this,
            $name
        ));
    }

    public function getNodes($role = null)
    {
        if ($role === null) {
            return $this['nodes'];
        }

        if (!is_string($role)) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s, role must be a string or null for all nodes',
                $this
            ));
        }

        if (!isset($this['roles'][$role])) {
            throw new OutOfBoundsException(sprintf(
                'Environment: %s, Role: %s doesn\'t exist',
                $this,
                $role
            ));
        }

        return $this['roles'][$role];
    }
}
