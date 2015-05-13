<?php

namespace spec\Assimtech\Tempo\Factory;

use PhpSpec\ObjectBehavior;

class EnvironmentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Factory\EnvironmentFactory');
    }

    function it_can_create_environments($node1)
    {
        $node1->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node1->__toString()->willReturn('node1');

        $env1Config = array(
            'name' => 'env1',
            'nodes' => array(
                'node1',
            ),
            'roles' => array(
                'test-role' => array(
                    'node1',
                ),
            ),
        );
        $envConfig = array(
            $env1Config,
        );
        $nodes = array(
            'node1' => $node1,
        );
        $this->create($envConfig, $nodes)->shouldHaveCount(1);
    }
}
