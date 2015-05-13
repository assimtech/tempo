<?php

namespace Assimtech\Tempo;

use Assimtech\Tempo\ArrayObject\ValidatableArrayObject;
use InvalidArgumentException;
use OutOfBoundsException;

class Environment extends ValidatableArrayObject
{
    /**
     * {@inheritdoc}
     * Example:
     *  // $node1, $node2, $node3, $node4 are instances of \Assimtech\Tempo\Node\NodeInterface
     *  new Environment(array(
     *      'name' => 'test',
     *      'nodes' => array(
     *          $node1,
     *          $node2,
     *          $node3,
     *          $node4,
     *      ),
     *      'roles' => array(
     *          'role1' => array(
     *              $node1,
     *              $node2,
     *          ),
     *          'role2' => array(
     *              $node3,
     *          ),
     *      ),
     *  ))
     */
    public function __construct($input = array(), $flags = 0, $iteratorClass = 'ArrayIterator')
    {
        // Handle string shortcut setup
        if (is_string($input)) {
            $input = array(
                'name' => $input,
            );
        }

        // Defaults
        if (is_array($input)) {
            $input = array_replace_recursive(array(
                'nodes' => array(),
                'roles' => array(),
            ), $input);
        }

        parent::__construct($input, $flags, $iteratorClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($index = null)
    {
        if ($index === null || $index === 'name') {
            $this->validateName();
        }

        if ($index === null || $index === 'nodes') {
            $this->validateNodes();
        }

        if ($index === null || $index === 'roles') {
            $this->validateRoles();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateName()
    {
        if (!isset($this['name']) || empty($this['name'])) {
            throw new InvalidArgumentException('property: [name] is mandatory');
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateNodes()
    {
        if (!isset($this['nodes'])) {
            throw new InvalidArgumentException('property: [nodes] is mandatory');
        }

        $foundNodes = array();
        foreach ($this['nodes'] as $idx => $node) {
            if (!$node instanceof Node\NodeInterface) {
                throw new InvalidArgumentException(sprintf(
                    'property: [nodes] must implement \Assimtech\Tempo\Node\NodeInterface, [nodes][%s] is a %s',
                    $idx,
                    is_object($node) ? get_class($node) : gettype($node)
                ));
            }

            if (in_array((string)$node, $foundNodes)) {
                throw new InvalidArgumentException(sprintf(
                    'property: [nodes][] contains a duplicate node: %s',
                    (string)$node
                ));
            }

            $foundNodes[] = (string)$node;
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateRoles()
    {
        if (!isset($this['roles'])) {
            throw new InvalidArgumentException('property: [roles] is mandatory');
        }

        foreach ($this['roles'] as $role => $nodes) {
            foreach ($nodes as $idx => $node) {
                if (!in_array($node, $this['nodes'])) {
                    throw new InvalidArgumentException(sprintf(
                        'property: [roles][%s][%s] (%s) is not a member of [nodes][]',
                        $role,
                        $idx,
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
     * @param \Assimtech\Tempo\Node\NodeInterface $node
     * @param string|array $roles Optional for grouping of like nodes e.g. fep, web, db
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addNode(Node\NodeInterface $node, $roles = array())
    {
        if (is_string($roles)) {
            $roles = array(
                $roles,
            );
        }

        if (!is_array($roles)) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s, roles must be a string or an array of strings',
                $this
            ));
        }

        foreach ($roles as $role) {
            if (!is_string($role)) {
                throw new InvalidArgumentException(sprintf(
                    'Environment: %s, roles must be a string or an array of strings',
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
     * @param \Assimtech\Tempo\Node\NodeInterface[] $nodes
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
     * @return \Assimtech\Tempo\Node\NodeInterface
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
