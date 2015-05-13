<?php

namespace Assimtech\Tempo\Test;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Definition;
use Symfony\Component\Console\Command\Command;

class DefinitionTest extends PHPUnit_Framework_TestCase
{
    public function testSetGetCommand()
    {
        $tempo = new Definition();

        $command = new Command('test');

        $tempo->addCommand($command);

        $this->assertContains($command, $tempo->getCommands());
    }

    public function testSetGetCommands()
    {
        $tempo = new Definition();

        $command1 = new Command('test1');
        $command2 = new Command('test2');

        $commands = array(
            $command1,
            $command2,
        );

        $tempo->addCommands($commands);

        $this->assertEquals($commands, $tempo->getCommands());
    }
}
