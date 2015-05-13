<?php

namespace Assimtech\Tempo\Factory;

use Assimtech\Tempo\Node;

/**
 * Constructs Nodes from configuration
 */
class NodeFactory
{
    /**
     * @param array $config
     * @return \Assimtech\Tempo\Node\NodeInterface[]
     */
    public function create(array $config)
    {
        $nodes = array();

        foreach ($config as $nodeName => $nodeConfig) {
            $nodes[$nodeName] = new Node\Remote($nodeConfig);
        }

        return $nodes;
    }
}
