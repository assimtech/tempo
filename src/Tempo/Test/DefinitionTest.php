<?php

namespace Tempo\Test;

use PHPUnit_Framework_TestCase;
use Tempo\Definition;

class DefinitionTest extends PHPUnit_Framework_TestCase
{
    public function testEnvironment()
    {
        $environmentName = 'test';

        $tempo = new Definition();

        $mockEnvironment = $this->getMock('Tempo\Environment', array('__toString'), array($environmentName));
        $mockEnvironment
            ->expects($this->exactly(2))
            ->method('__toString')
            ->will($this->returnValue($environmentName))
        ;
        $tempo->addEnvironment($mockEnvironment);

        $this->assertEquals($mockEnvironment, $tempo->getEnvironment($environmentName));
    }
}
