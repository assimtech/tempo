<?php

namespace spec\Assimtech\Tempo;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Command\Command;

class DefinitionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Definition');
    }

    function it_can_be_constructed_with_commands(Command $command1, Command $command2)
    {
        $commands = array(
            $command1,
            $command2,
        );
        $this->beConstructedWith($commands);
        $this->getCommands()->shouldReturn($commands);
    }

    function it_can_be_constructed_without_commands()
    {
        $this->beConstructedWith();
        $this->getCommands()->shouldReturn(array());
    }

    function it_takes_a_command(Command $command)
    {
        $this->addCommand($command)->shouldReturn($this);
        $this->getCommands()->shouldContain($command);
    }

    function it_takes_commands(Command $command)
    {
        $this->addCommands(array($command))->shouldReturn($this);
        $this->getCommands()->shouldContain($command);
    }
}
