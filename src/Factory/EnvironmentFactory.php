<?php

namespace Assimtech\Tempo\Factory;

use Assimtech\Tempo\Environment;

/**
 * Constructs Environments from configuration
 */
class EnvironmentFactory
{
    /**
     * @param array $config
     * @param \Assimtech\Tempo\Node\NodeInterface[] $nodes
     * @return \Assimtech\Tempo\Environment[]
     */
    public function create(array $config, array $nodes)
    {
        $environments = array();

        foreach ($config as $environmentConfig) {
            $environments[] = $this->constructEnvironment(
                $environmentConfig,
                $nodes
            );
        }

        return $environments;
    }

    protected function constructEnvironment($environmentConfig, $nodes)
    {
        // Replace node names with node instances
        if (isset($environmentConfig['nodes'])) {
            foreach ($environmentConfig['nodes'] as $i => $nodeName) {
                $environmentConfig['nodes'][$i] = $nodes[$nodeName];
            }
        }

        // Replace node names with node instances
        if (isset($environmentConfig['roles'])) {
            foreach ($environmentConfig['roles'] as $role => $nodeNames) {
                foreach ($nodeNames as $i => $nodeName) {
                    $environmentConfig['roles'][$role][$i] = $nodes[$nodeName];
                }
            }
        }

        return new Environment($environmentConfig);
    }
}
