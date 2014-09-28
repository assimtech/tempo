# Environments

An environment is a group of server(s) where your software may be deployed to. It is defined simply by a name.

Common examples include:

*   staging
*   testing
*   demo
*   production

To define an environment:

    $production = new Assimtech\Tempo\Environment('production');
    $tempo->addEnvironment($production);


## An environment is an ArrayObject child

A tempo environment extends [ArrayObject](http://php.net/manual/en/class.arrayobject.php), this means you can store
properties on it and access them later.

    $staging['webpath'] = '/var/www/staging.example.com';
    $production['webpath'] = '/var/www/example.com';

    // ... Later in a task or command definition ...
    doSomethingTo($env['webpath']);


## Working with nodes

An Environment is a collection of one or more [Nodes](04-Nodes.md). You can add, remove and group (see Roles)
[Nodes](04-Nodes.md) within an environment.


### Adding nodes

To add a [Node](04-Nodes.md) to an environment:

    $environment->addNode($node);

If you have multiple [nodes](04-Nodes.md), they can all be added at once:

    $environment->addNodes(array(
        $node1,
        $node2,
    ));


### Getting nodes

Once an environment has some [nodes]](04-Nodes.md) they can be fetched.
This is typically done in a [Command](05-Commands.md) or [Task](06-Tasks.md).

    // This would only work if you only have 1 node in your environment
    $node = $environment->getNode();

    // If you have more than 1 node you will need to
    $node = $environment->get('nodename');

    // More likely, you would get an array of all the nodes in the environment
    $nodes = $environment->getNodes();


### Roles

Sometimes it makes sense to group [nodes](04-Nodes.md) within an environment, for this we have roles. A role is nothing
more than a name for a group of [nodes](04-Nodes.md).


#### Adding nodes

To assign a role to a [node](04-Nodes.md), it must be given while adding the [node(s)](04-Nodes.md)

    $environment->addNode($node, 'db');

    $environment->addNodes(array(
        $node1,
        $node2,
    ), 'web');


#### Getting nodes

This will allow us to later get [nodes](04-Nodes.md) from the environment by the role name.

    // An array of all the 'db' nodes
    $databaseNodes = $environment->getNodes('db');

    // An array of all the 'web' nodes
    $webNodes = $environment->getNodes('web');


#### Multi role nodes

If you have [node(s)](04-Nodes.md) that act as multiple roles, you could define all the roles when adding them.

    $environment->addNode($node, array(
        'db',
        'cache',
    ));

    $environment->addNodes(array(
        $node1,
        $node2,
    ), array(
        'web',
        'cron',
    ));
