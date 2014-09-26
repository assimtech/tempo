# tempo

[![Build Status](https://travis-ci.org/kralos/tempo.svg?branch=master)](https://travis-ci.org/kralos/tempo)

Automated deployment for server side software

Tempo allows you to express how software is deployed to your servers using a few simple definitions.


## Quick start

Download `tempo.phar`. If you place it in `~/bin/tempo` and make it executable you
will be able to run `tempo` from any of your projects.

Create a `tempo.php` file in the root of your project containing the following:

    <?php

    $tempo = new Tempo\Definition();

    // Environments
    $environment1 = new Tempo\Environment('test');
    $tempo->addEnvironment($environment1);

    // Nodes
    $server1 = new Tempo\Node\Remote('server1.example.com');
    $environment1->addNode($server1);

    // Commands
    foreach ($tempo->getEnvironments() as $environment) {
        $whoami = new Symfony\Component\Console\Command\Command($environment.':whoami');
        $whoami->setCode(function ($input, $output) use ($environment) {
            $node = $environment->getNode();

            $iam = $node->run('whoami');
            $output->write($iam);
        });
        $tempo->addCommand($whoami);

        $whereami = new Symfony\Component\Console\Command\Command($environment.':whereami');
        $whereami->setCode(function ($input, $output) use ($environment) {
            $node = $environment->getNode();

            $iam = $node->run('hostname');
            $output->write($iam);
        });
        $tempo->addCommand($whereami);
    }

    return $tempo;


Change "server1.example.com" to a server you have ssh access to.
If you need to change usernames, please see how to define setup the [Node](docs/02-Nodes.md)

Run tempo from within the root of your project:

    tempo test:whoami
    tempo test:whereami


Try adding more environments / servers / commands


As you might expect, the `tempo.php` will eventually become a little bloated so we recommend splitting it up.
Please see the [documentation](docs/01-About.md) for how you can better define your tempo objects.
The main objective of tempo is to provide a clear concise tool for expressing these definitions and some reusable
pre-defined definitions in the form of Commands and Tasks.

Please see documentation for advanced usage:

* [About](docs/01-About.md)
* [Installation](docs/02-Installation.md)
* [Environments](docs/03-Environments.md)
* [Nodes](docs/04-Nodes.md)
* [Commands](docs/05-Commands.md)
* [Tasks](docs/06-Tasks.md)
