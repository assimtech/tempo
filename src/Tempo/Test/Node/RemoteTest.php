<?php

namespace Tempo\Test\Node;

use PHPUnit_Framework_TestCase;
use Tempo\Node\Remote;

class RemoteTest extends PHPUnit_Framework_TestCase
{
    public function testHost()
    {
        $host = 'localhost';

        $node = new Remote($host);

        $this->assertEquals("$host", (string)$node);
    }

    public function testUserAtHost()
    {
        $userAtHost = 'user@localhost';

        $node = new Remote($userAtHost);

        $this->assertEquals($userAtHost, (string)$node);
    }

    public function testUserHost()
    {
        $user = 'user';
        $host = 'localhost';

        $node = new Remote(array(
            'host' => $host,
            'user' => $user,
        ));

        $this->assertEquals("$user@$host", (string)$node);
    }
}
