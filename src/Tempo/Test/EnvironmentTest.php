<?php

namespace Tempo\Test;

use PHPUnit_Framework_TestCase;
use Tempo\Environment;
use Tempo\Node\Local;
use Tempo\Node\Remote;

class EnvironmentTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $environmentName = 'test';

        $environment = new Environment($environmentName);

        $this->assertEquals($environmentName, (string)$environment);
    }

    public function testAddNode()
    {
        $node = new Local();

        $environment = new Environment('test');

        $environment->addNode($node);

        $this->assertEquals($node, $environment->getNode());
    }

    public function testAddNodeWithRole()
    {
        $node = new Local();
        $role = 'myrole';

        $environment = new Environment('test');

        $environment->addNode($node, $role);

        $this->assertContains($node, $environment->getNodes($role));
    }

    public function testAddNodeWithRoles()
    {
        $node = new Local();
        $role1 = 'myrole';
        $role2 = 'yourrole';

        $environment = new Environment('test');

        $environment->addNode($node, array(
            $role1,
            $role2,
        ));

        $this->assertContains($node, $environment->getNodes($role1));
        $this->assertContains($node, $environment->getNodes($role2));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Environment: test, roles must be a string or array of strings
     */
    public function testAddNodeWithInvalidRole()
    {
        $node = new Local();
        $role = 1;

        $environment = new Environment('test');

        $environment->addNode($node, $role);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Environment: test, roles must be a string or array of strings
     */
    public function testAddNodeWithInvalidRoles()
    {
        $node = new Local();
        $role = 1;

        $environment = new Environment('test');

        $environment->addNode($node, array($role));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Environment: test, Node: localhost already exists
     */
    public function testAddDuplicateNode()
    {
        $node = new Local();

        $environment = new Environment('test');

        $environment
            ->addNode($node)
            ->addNode($node)
        ;
    }

    public function testGetNodeByName()
    {
        $node = new Local();

        $environment = new Environment('test');

        $environment->addNode($node);

        $this->assertEquals($node, $environment->getNode('localhost'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You must specify the node name because environment test has more than 1 node:
     * @expectedExceptionMessage localhost, remotehost
     */
    public function testGetNodesWithoutName()
    {
        $localhost = new Local();
        $remotehost = new Remote('remotehost');

        $environment = new Environment('test');

        $environment
            ->addNode($localhost)
            ->addNode($remotehost)
        ;

        $environment->getNode();
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Environment: test, Node: localhost doesn't exist
     */
    public function testGetNodeWithoutNodes()
    {
        $environment = new Environment('test');

        $environment->getNode('localhost');
    }

    public function testGetNodes()
    {
        $node = new Local();

        $environment = new Environment('test');

        $environment->addNode($node);

        $this->assertContains($node, $environment->getNodes());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Environment: test, role must be a string or null for all nodes
     */
    public function testGetNodesByInvalidRole()
    {
        $node = new Local();

        $environment = new Environment('test');

        $environment->addNode($node);

        $environment->getNodes(1);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Environment: test, Role: nonexistant doesn't exist
     */
    public function testGetNodesByNonexistantRole()
    {
        $node = new Local();

        $environment = new Environment('test');

        $environment->addNode($node);

        $environment->getNodes('nonexistant');
    }
}
