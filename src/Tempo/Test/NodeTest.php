<?php

namespace Tempo\Test;

use PHPUnit_Framework_TestCase;
use Tempo\Node;

class NodeTest extends PHPUnit_Framework_TestCase
{
    public function testHost()
    {
        $host = 'localhost';

        $node = new Node($host);

        $this->assertEquals("$host", (string)$node);
    }

    public function testUserHost()
    {
        $user = 'user';
        $host = 'localhost';

        $node = new Node($host, $user);

        $this->assertEquals("$user@$host", (string)$node);
    }
}
