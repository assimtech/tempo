<?php

namespace spec\Assimtech\Tempo\Task;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractTaskSpec extends ObjectBehavior
{
    function let(InputInterface $input, OutputInterface $output)
    {
        require_once __DIR__ . '/AbstractTaskSubClass.php';
        $this->beAnInstanceOf('spec\Assimtech\Tempo\Task\AbstractTaskSubClass');
        $this->beConstructedWith($input, $output);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Task\AbstractTask');
    }
}
