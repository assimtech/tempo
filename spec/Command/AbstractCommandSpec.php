<?php

namespace spec\Assimtech\Tempo\Command;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Assimtech\Tempo\Environment;

class AbstractCommandSpec extends ObjectBehavior
{
    function let()
    {
        require_once __DIR__ . '/AbstractCommandSubClass.php';
        $this->beAnInstanceOf('spec\Assimtech\Tempo\Command\AbstractCommandSubClass');
    }

    function it_is_initializable(Environment $env)
    {
        $envName = 'test-env';
        $env->__toString()->willReturn($envName);

        $this->beConstructedWith($env);
        $this->shouldHaveType('Assimtech\Tempo\Command\AbstractCommand');
    }

    function it_can_be_named(Environment $env)
    {
        $envName = 'test-env';
        $env->__toString()->willReturn($envName);

        $name = 'command-name';

        $this->beConstructedWith($env, $name);
        $this->getName()->shouldReturn(sprintf('%s:%s', $envName, $name));
    }

    function it_can_assume_its_own_name(Environment $env)
    {
        $envName = 'test-env';
        $env->__toString()->willReturn($envName);

        $this->beConstructedWith($env);
        $this->getName()->shouldReturn(sprintf('%s:%s', $envName, strtolower('AbstractCommandSubClass')));
    }
}
