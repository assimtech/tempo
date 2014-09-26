# Nodes

A node is a singular host / server (be it physical or virtual) where a single copy of your software is deployed.

A node can be defined by any host or IP address which is valid in the network you are deploying from.

    // tempo.php

    $tempo = new Tempo\Tempo();

    $production = new Tempo\Environment('production');
    $tempo->addEnvironment($production);

    $server = new Tempo\Node\Remote('example.com');
    $production->addNode($server);

    return $tempo;


Optionally, a node can also be given a role or multiple roles when registered in an environment

    // tempo.php

    // ...

    // Give our node a single role
    $server = new Tempo\Node\Remote('example.com');
    $production->addNode($server, 'web');

    // Or many at once
    $production->addNode($server, array(
        'db',
        'cache',
    ));

    // ...


If you have assigned role(s) to a node, you will later be able to get all your 'role' node in your command definitions:

    // tempo.php

    // ...

    $webNodes = $production->getNodes('web');
    foreach ($webNodes as $webNode) {
        $webNode->run($someCommand);
    }

    // ...