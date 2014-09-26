# tempo

[![Build Status](https://travis-ci.org/kralos/tempo.svg?branch=master)](https://travis-ci.org/kralos/tempo)

Automated deployment for server side software

Tempo allows you to express how software is deployed to your servers using a few simple definitions.


## Quick start

Download `tempo.phar`. If you place it in `~/bin/tempo` and make it executable you
will be able to execute `tempo` from any of your projects.

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


Change "server1.example.com" to a server you have access to ssh to as your user

Run tempo from within the root of your project:

    tempo test:whoami
    tempo test:whereami

Try adding more environments / servers / commands


As you might expect, the `tempo.php` will eventually become a little bloated so we recommend splitting it up.
The main objective of tempo is to provide a clear concise tool for expressing these definitions and some reusable
pre-defined definitions in the form of Commands and Tasks.

Please see documentation for advanced usage:

* [Installation](docs/00-Installation.md)
* [Environments](docs/01-Environments.md)
* [Nodes](docs/02-Nodes.md)
* [Commands](docs/03-Commands.md)
* [Tasks](docs/04-Tasks.md)
