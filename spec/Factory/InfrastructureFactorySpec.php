<?php

namespace spec\Assimtech\Tempo\Factory;

use PhpSpec\ObjectBehavior;
use Assimtech\Tempo\Factory;
use InvalidArgumentException;

class InfrastructureFactorySpec extends ObjectBehavior
{
    function let(Factory\NodeFactory $nodeFactory, Factory\EnvironmentFactory $envFactory)
    {
        $this->beConstructedWith($nodeFactory, $envFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Factory\InfrastructureFactory');
    }

    function it_cant_create_without_nodes()
    {
        $this->shouldThrow(new InvalidArgumentException("config: [nodes] is mandatory"))->during('create', array(
            array(),
        ));
    }

    function it_cant_create_without_environments()
    {
        $this->shouldThrow(new InvalidArgumentException("config: [environments] is mandatory"))->during('create', array(
            array(
                'nodes' => array(),
            ),
        ));
    }

    function it_can_create(Factory\NodeFactory $nodeFactory, Factory\EnvironmentFactory $envFactory)
    {
        $nodesConfig = array('nodes-value');
        $environmentsConfig = array('environments-value');
        $nodes = array('nodes');
        $environments = array();

        $nodeFactory->create($nodesConfig)->willReturn($nodes);
        $envFactory->create($environmentsConfig, $nodes)->willReturn($environments);

        $this->create(array(
            'nodes' => $nodesConfig,
            'environments' => $environmentsConfig,
        ))->shouldReturnAnInstanceOf('Assimtech\Tempo\Infrastructure');
    }
}
