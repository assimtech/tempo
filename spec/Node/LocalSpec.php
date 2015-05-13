<?php

namespace spec\Assimtech\Tempo\Node;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LocalSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Node\Local');
    }

    function it_can_run()
    {
        $command = 'hostname';
        $output = `$command`;

        $this->run($command)->shouldReturn($output);
    }

    function it_can_be_casted_to_string()
    {
        $this->__toString()->shouldReturn('localhost');
    }
}
