<?php

namespace Tempo\Test;

use PHPUnit_Framework_TestCase;
use Tempo\Node;

class NodeTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $user = 'user';
        $host = 'localhost';

        $node = new Node($user, $host);

        $this->assertEquals("$user@$host", (string)$node);
    }
}
