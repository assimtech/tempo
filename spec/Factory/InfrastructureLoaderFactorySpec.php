<?php

namespace spec\Assimtech\Tempo\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InfrastructureLoaderFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Factory\InfrastructureLoaderFactory');
    }

    function it_can_create()
    {
        $this->create()->shouldReturnAnInstanceOf('Assimtech\Tempo\Loader\InfrastructureLoader');
    }
}
