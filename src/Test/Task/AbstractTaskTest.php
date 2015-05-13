<?php

namespace Assimtech\Tempo\Test\Task;

use PHPUnit_Framework_TestCase;

class AbstractTaskTest extends PHPUnit_Framework_TestCase
{
    public function testAbstractTask()
    {
        $mockInputInterface = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $mockOutputInterface = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $mockTask = $this->getMockForAbstractClass('Assimtech\Tempo\Task\AbstractTask', array(
            $mockInputInterface,
            $mockOutputInterface,
        ));

        $this->assertInstanceOf('Assimtech\Tempo\Task\AbstractTask', $mockTask);
    }
}
