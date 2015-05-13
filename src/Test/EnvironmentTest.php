<?php

namespace Assimtech\Tempo\Test;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Environment;
use Assimtech\Tempo\Node\Local;
use Assimtech\Tempo\Node\Remote;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class EnvironmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage property: [name] is mandatory
     */
    public function testMissingName()
    {
        $config = array(
        );

        new Environment($config);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ty: [nodes] must implement \Assimtech\Tempo\Node\NodeInterface, [nodes][0] is a integer
     */
    public function testInvalidNode()
    {
        $config = array(
            'name' => 'test',
            'nodes' => array(
                1,
            ),
        );

        new Environment($config);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage property: [nodes][] contains a duplicate node: server1
     */
    public function testDuplicateNode()
    {
        $config = array(
            'name' => 'test',
            'nodes' => array(
                new Remote('server1'),
                new Remote('server1'),
            ),
        );

        new Environment($config);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage property: [roles][web][0] (server1) is not a member of [nodes][]
     */
    public function testRoleMissingNode()
    {
        $config = array(
            'name' => 'test',
            'roles' => array(
                'web' => array(
                    new Remote('server1')
                ),
            ),
        );

        new Environment($config);
    }

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
     * @expectedExceptionMessage Environment: test, roles must be a string or an array of strings
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
     * @expectedExceptionMessage Environment: test, roles must be a string or an array of strings
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

    public function testAddNodes()
    {
        $localhost = new Local();
        $remotehost = new Remote('remotehost');

        $environment = new Environment('test');

        $nodes = array(
            $localhost,
            $remotehost,
        );
        $environment->addNodes($nodes);

        $this->assertEquals($nodes, array_values($environment->getNodes()));
    }

    public function testAddNodesWithRoles()
    {
        $localhost = new Local();
        $remotehost = new Remote('remotehost');
        $role = 'myrole';

        $environment = new Environment('test');

        $nodes = array(
            $localhost,
            $remotehost,
        );
        $environment->addNodes($nodes, $role);

        $this->assertEquals($nodes, array_values($environment->getNodes($role)));
    }
}
