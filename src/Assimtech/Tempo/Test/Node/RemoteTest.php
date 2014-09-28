<?php

namespace Assimtech\Tempo\Test\Node;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Node\Remote;

class RemoteTest extends PHPUnit_Framework_TestCase
{
    public function testHost()
    {
        $host = 'localhost';

        $node = new Remote($host);

        $this->assertEquals("$host", (string)$node);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage host is mandatory
     */
    public function testNoHost()
    {
        $node = new Remote('');
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
