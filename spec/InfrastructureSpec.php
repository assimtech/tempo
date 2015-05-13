<?php

namespace spec\Assimtech\Tempo;

use PhpSpec\ObjectBehavior;
use Assimtech\Tempo\Environment;
use InvalidArgumentException;
use OutOfBoundsException;

class InfrastructureSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Infrastructure');
    }

    function it_cant_add_a_duplicate_environment(Environment $env)
    {
        $envName = 'env-name';
        $env->__toString()->willReturn($envName);

        $this->addEnvironment($env)->shouldReturn($this);
        $this->getEnvironment($envName)->shouldReturn($env);

        $this->shouldThrow(new InvalidArgumentException("Environment: $envName already exists"))->during('addEnvironment', array(
            $env,
        ));
    }

    function it_can_add_an_environment(Environment $env)
    {
        $envName = 'env-name';
        $env->__toString()->willReturn($envName);

        $this->addEnvironment($env)->shouldReturn($this);
        $this->getEnvironment($envName)->shouldReturn($env);
    }

    function it_can_add_environments(Environment $env1, Environment $env2)
    {
        $env1Name = 'env1';
        $env1->__toString()->willReturn($env1Name);
        $env2Name = 'env2';
        $env2->__toString()->willReturn($env2Name);

        $allEnvs = array(
            $env1,
            $env2,
        );

        $this->addEnvironments($allEnvs)->shouldReturn($this);
        $this->getEnvironments()->shouldContain($env1);
        $this->getEnvironments()->shouldContain($env2);
    }

    function it_cant_get_a_nonexistent_environment()
    {
        $envName = 'nonexistent';
        $this->shouldThrow(new OutOfBoundsException("Environment: $envName doesn't exist"))->during('getEnvironment', array(
            $envName,
        ));
    }
}
