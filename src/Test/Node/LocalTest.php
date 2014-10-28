<?php

namespace Assimtech\Tempo\Test\Node;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Node\Local;

class LocalTest extends PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $node = new Local();
        $command = sprintf(
            'ls -a %s',
            __DIR__
        );
        $expectedOutput = scandir(__DIR__);

        $output = $node->run($command);

        foreach ($expectedOutput as $expectedItem) {
            $this->assertContains($expectedItem, $output);
        }
    }

    public function testName()
    {
        $node = new Local();

        $this->assertEquals('localhost', (string)$node);
    }
}
