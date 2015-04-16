<?php

namespace Assimtech\Tempo\Test;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Environment;

class AbstractCommandTest extends PHPUnit_Framework_TestCase
{
    public function argsProvider()
    {
        $env = new Environment('test');

        $commandsArgs = array(
            array(array($env)),
            array(array($env, 'mycommand')),
        );

        return $commandsArgs;
    }

    /**
     * @dataProvider argsProvider
     */
    public function testConstructs($args)
    {
        $command = $this->getMockForAbstractClass(
            'Assimtech\Tempo\Command\AbstractCommand',
            $args,
            'MyCommand'
        );
        $this->assertInstanceOf('Assimtech\Tempo\Command\AbstractCommand', $command);
        $this->assertEquals('test:mycommand', $command->getName());
    }
}
