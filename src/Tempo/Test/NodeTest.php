<?php

namespace Tempo\Test;

use PHPUnit_Framework_TestCase;
use Tempo\Node;

class NodeTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $host = 'localhost';

        $node = new Node($host);

        $this->assertEquals($host, (string)$node);
    }
}
