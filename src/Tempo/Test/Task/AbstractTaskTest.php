<?php

namespace Tempo\Test\Task;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractTaskTest extends PHPUnit_Framework_TestCase
{
    public function testAbstractTask()
    {
        $mockInputInterface = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $mockOutputInterface = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $mockTask = $this->getMockForAbstractClass('Tempo\Task\AbstractTask', array(
            $mockInputInterface,
            $mockOutputInterface,
        ));

        $this->assertInstanceOf('Tempo\Task\AbstractTask', $mockTask);
    }
}
