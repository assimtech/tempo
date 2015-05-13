<?php

namespace spec\Assimtech\Tempo\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NodeFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Factory\NodeFactory');
    }

    function it_can_create()
    {
        $config = array(
            'node1' => 'node1',
        );
        $this->create($config)->shouldHaveCount(1);
    }
}
