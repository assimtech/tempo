<?php

namespace spec\Assimtech\Tempo;

use PhpSpec\ObjectBehavior;
use InvalidArgumentException;
use OutOfBoundsException;

class EnvironmentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Environment');
    }

    function it_cant_be_constructed_without_a_name()
    {
        $this->shouldThrow(new InvalidArgumentException("property: [name] is mandatory"))->during('__construct');
    }

    function it_can_be_constructed_with_defaults()
    {
        $name = 'test';
        $this->beConstructedWith($name);
        $this->getArrayCopy()->shouldHaveKeyWithValue('name', $name);
        $this->getArrayCopy()->shouldHaveKeyWithValue('nodes', array());
        $this->getArrayCopy()->shouldHaveKeyWithValue('roles', array());
    }

    function it_can_be_constructed_with_nodes_and_roles($node1, $node2, $node3)
    {
        $name = 'test';
        $roleName = 'role-name';
        $node1->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node1->__toString()->willReturn('node1');
        $node2->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node2->__toString()->willReturn('node2');
        $node3->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node3->__toString()->willReturn('node3');

        $allNodes = array(
            $node1,
            $node2,
            $node3,
        );
        $roleNodes = array(
            $node1,
            $node2,
            $node3,
        );
        $this->beConstructedWith(array(
            'name' => $name,
            'nodes' => $allNodes,
            'roles' => array(
                $roleName => $roleNodes,
            ),
        ));
        $this->getArrayCopy()->shouldHaveKeyWithValue('name', $name);
        $this->getArrayCopy()->shouldHaveKeyWithValue('nodes', $allNodes);
        $this->getArrayCopy()->shouldHaveKeyWithValue('roles', array(
            $roleName => $roleNodes,
        ));
    }

    function it_must_have_a_name_property()
    {
        $this->beConstructedWith('test');
        $this->shouldThrow(new InvalidArgumentException("property: [name] is mandatory"))->during('offsetUnset', array(
            'name',
        ));
    }

    function it_must_have_a_nodes_property()
    {
        $this->beConstructedWith('test');
        $this->shouldThrow(new InvalidArgumentException("property: [nodes] is mandatory"))->during('offsetUnset', array(
            'nodes',
        ));
    }

    function it_doesnt_need_a_foo_property()
    {
        $this->beConstructedWith('test');
        $this->offsetSet('foo', 'bar');
        $this->offsetGet('foo')->shouldReturn('bar');
        $this->offsetUnset('foo');
        $this->offsetExists('foo')->shouldReturn(false);
    }

    function its_nodes_must_be_instances_of()
    {
        $this->beConstructedWith('test');
        $this->shouldThrow(new InvalidArgumentException("property: [nodes] must implement \Assimtech\Tempo\Node\NodeInterface, [nodes][0] is a string"))->during('offsetSet', array(
            'nodes',
            array(
                'invalid',
            ),
        ));
    }

    function its_nodes_must_not_be_duplicated($node1, $node2)
    {
        $nodeName = 'node-name';
        $node1->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node1->__toString()->willReturn($nodeName);
        $node2->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node2->__toString()->willReturn($nodeName);

        $this->beConstructedWith('test');
        $this->shouldThrow(new InvalidArgumentException("property: [nodes][] contains a duplicate node: $nodeName"))->during('offsetSet', array(
            'nodes',
            array(
                $node1,
                $node2,
            ),
        ));
    }

    function it_must_have_a_roles_property()
    {
        $this->beConstructedWith('test');
        $this->shouldThrow(new InvalidArgumentException("property: [roles] is mandatory"))->during('offsetUnset', array(
            'roles',
        ));
    }

    function its_roles_must_contain_member_nodes($node)
    {
        $nodeName = 'node-name';
        $roleName = 'role-name';
        $node->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node->__toString()->willReturn($nodeName);

        $this->beConstructedWith('test');
        $this->shouldThrow(new InvalidArgumentException("property: [roles][$roleName][0] ($nodeName) is not a member of [nodes][]"))->during('offsetSet', array(
            'roles',
            array(
                $roleName => array(
                    $node,
                ),
            ),
        ));
    }

    function it_can_be_casted_to_string()
    {
        $name = 'test';
        $this->beConstructedWith($name);
        $this->__toString()->shouldReturn($name);
    }

    function it_can_add_a_node_to_a_single_role($node)
    {
        $nodeName = 'node-name';
        $roleName = 'role-name';
        $node->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node->__toString()->willReturn($nodeName);

        $this->beConstructedWith('test');
        $this->addNode($node, $roleName)->shouldReturn($this);
        $this->getNode($nodeName)->shouldReturn($node);
        $this->getNodes($roleName)->shouldReturn(array($node));
    }

    function it_cant_add_a_node_to_non_array_roles($node)
    {
        $name = 'test';
        $nodeName = 'node-name';
        $node->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node->__toString()->willReturn($nodeName);

        $this->beConstructedWith($name);
        $this->shouldThrow(new InvalidArgumentException("Environment: $name, roles must be a string or an array of strings"))->during('addNode', array(
            $node,
            false,
        ));
    }

    function it_cant_add_a_node_to_invalid_role($node)
    {
        $name = 'test';
        $nodeName = 'node-name';
        $roleName = false;
        $node->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node->__toString()->willReturn($nodeName);

        $this->beConstructedWith($name);
        $this->shouldThrow(new InvalidArgumentException("Environment: $name, roles must be a string or an array of strings"))->during('addNode', array(
            $node,
            array(
                $roleName,
            ),
        ));
    }

    function it_cant_add_duplicae_nodes($node)
    {
        $name = 'test';
        $nodeName = 'node-name';
        $node->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node->__toString()->willReturn($nodeName);

        $this->beConstructedWith($name);
        $this->addNode($node)->shouldReturn($this);
        $this->shouldThrow(new InvalidArgumentException("Environment: $name, Node: $nodeName already exists"))->during('addNode', array(
            $node,
        ));
    }

    function it_cant_get_a_nameless_node_without_exactly_one($node1, $node2)
    {
        $name = 'test';
        $node1Name = 'node1';
        $node1->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node1->__toString()->willReturn($node1Name);
        $node2Name = 'node2';
        $node2->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node2->__toString()->willReturn($node2Name);

        $this->beConstructedWith(array(
            'name' => $name,
            'nodes' => array(
                $node1,
                $node2,
            ),
        ));
        $this->shouldThrow(new InvalidArgumentException("You must specify the node name because environment $name has more than 1 node: $node1Name, $node2Name"))->during('getNode');
    }

    function it_can_get_a_nameless_node($node)
    {
        $name = 'test';
        $nodeName = 'node-name';
        $node->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node->__toString()->willReturn($nodeName);

        $this->beConstructedWith(array(
            'name' => $name,
            'nodes' => array(
                $node,
            ),
        ));
        $this->getNode()->shouldReturn($node);
    }

    function it_cant_get_a_nonexistant_node()
    {
        $name = 'test';
        $nodeName = 'node-name';

        $this->beConstructedWith(array(
            'name' => $name,
        ));
        $this->shouldThrow(new OutOfBoundsException("Environment: $name, Node: $nodeName doesn't exist"))->during('getNode', array(
            $nodeName,
        ));
    }

    function it_can_get_a_node($node)
    {
        $name = 'test';
        $nodeName = 'node-name';
        $node->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node->__toString()->willReturn($nodeName);

        $this->beConstructedWith(array(
            'name' => $name,
            'nodes' => array(
                $node,
            ),
        ));
        $this->getNode($nodeName)->shouldReturn($node);
    }

    function it_can_add_and_get_nodes($node1, $node2)
    {
        $node1->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node1->__toString()->willReturn('node1');
        $node2->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node2->__toString()->willReturn('node2');

        $allNodes = array(
            $node1,
            $node2,
        );

        $this->beConstructedWith('test');
        $this->addNodes($allNodes)->shouldReturn($this);
        $this->getNodes()->shouldReturn($allNodes);
    }

    function it_can_add_and_get_nodes_with_roles($node1, $node2, $node3)
    {
        $roleName = 'role-name';
        $node1->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node1->__toString()->willReturn('node1');
        $node2->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node2->__toString()->willReturn('node2');
        $node3->beADoubleOf('Assimtech\Tempo\Node\NodeInterface');
        $node3->__toString()->willReturn('node3');

        $roleNodes = array(
            $node1,
            $node2,
        );

        $this->beConstructedWith('test');
        $this->addNodes($roleNodes, $roleName)->shouldReturn($this);
        $this->addNode($node3)->shouldReturn($this);
        $this->getNodes($roleName)->shouldReturn($roleNodes);
    }

    function it_doesnt_return_nodes_for_an_invalid_role()
    {
        $name = 'test';
        $roleName = false;

        $this->beConstructedWith($name);
        $this->shouldThrow(new InvalidArgumentException("Environment: $name, role must be a string or null for all nodes"))->during('getNodes', array(
            $roleName,
        ));
    }

    function it_doesnt_return_nodes_for_an_nonexistent_role()
    {
        $name = 'test';
        $roleName = 'non-existent';

        $this->beConstructedWith($name);
        $this->shouldThrow(new OutOfBoundsException("Environment: $name, Role: $roleName doesn't exist"))->during('getNodes', array(
            $roleName,
        ));
    }
}
