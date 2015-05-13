# tempo

[![Build Status](https://travis-ci.org/assimtech/tempo.svg?branch=master)](https://travis-ci.org/assimtech/tempo)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assimtech/tempo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assimtech/tempo/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assimtech/tempo/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assimtech/tempo/?branch=master)


Tempo is a scripting tool for running commands on local or remote nodes. It was originally developed to script complex
`php` project deployments however can be used for all kinds of tasks (e.g. unix user management, package updates etc).


## Quick start

Download [tempo.phar](https://github.com/assimtech/tempo/releases/download/0.1.0/tempo.phar).
If you place it in `~/bin/tempo` and make it executable you will be able to run `tempo` from any of your projects.


Create a `tempo.php` file in the root of your project containing the following:

```php
<?php

use Assimtech\Tempo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$tempo = new Tempo\Definition();

// Environments
$env1 = new Tempo\Environment('test');
$tempo->addEnvironment($env1);

// Nodes
$server1 = new Tempo\Node\Remote('server1.example.com');
$env1->addNode($server1);

// Commands
foreach ($tempo->getEnvironments() as $env) {
    $whereami = new Command($env.':whereami');
    $whereami->setCode(function (InputInterface $input, OutputInterface $output) use ($env) {
        foreach ($env->getNodes() as $node) {
            $output->write('I\'m on: ');
            $hostname = $node->run('hostname --fqdn');
            $output->writeln($hostname);

            $ips = $node->run('/sbin/ifconfig');
            $output->write($ips);
        }
    });
    $tempo->addCommand($whereami);
}

return $tempo;
```

Change "server1.example.com" to a server you have ssh access to.
If you need to change username / port etc, please see the documentation on how to setup a [Node](docs/04-Nodes.md)


Run tempo from within the root of your project:

```shell
tempo test:whereami
```

Try adding more environments / servers / commands


## A better layout

As you might expect, the `tempo.php` will eventually become a little bloated. Use pre-defined
[Commands](docs/05-Commands.md) and [Tasks](docs/06-Tasks.md) to help save some time and make the definition easier
to read. If you have a Task that should part of the core Tempo code base [please let us know](docs/07-Contributing.md).
It would also be better to split up the environment and node definitions and each command for readability.

Create a `tempo.php` in the root of your project containing:

```php
<?php

use Assimtech\Tempo;
use Symfony\Component\Console\Command\Command;

$tempo = new Tempo\Definition(__DIR__ . '/tempo/infrastructure.yml');

// Commands
foreach ($tempo->getEnvironments() as $env) {
    $whereami = new Command($env.':whereami');
    $whereami->setCode(require 'tempo/whereami.php');

    $tempo->addCommands(array(
        $whereami,
    ));
}

return $tempo;
```

Then create a `tempo/tempo.yml` containing:

```yaml
nodes:
    server1:
        ssh:
            host: server1.example.com

environments:
    -
        name: test
        nodes: [ server1 ]
```

And finally create your whereami command in `tempo/whereami.php`:

```php
<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @param \Symfony\Component\Console\Input\InputInterface $input
 * @param \Symfony\Component\Console\Output\OutputInterface $output
 * @var \Assimtech\Tempo\Environment $environment
 */
return function (InputInterface $input, OutputInterface $output) use ($env) {
    foreach ($env->getNodes() as $node) {
        $output->write('I\'m on: ');
        $hostname = $node->run('hostname --fqdn');
        $output->writeln($hostname);

        $ips = $node->run('/sbin/ifconfig');
        $output->write($ips);
    }
};
```

## Documentation

* [Overview](docs/01-Overview.md)
* [Installation](docs/02-Installation.md)
* [Environments](docs/03-Environments.md)
* [Nodes](docs/04-Nodes.md)
* [Commands](docs/05-Commands.md)
* [Tasks](docs/06-Tasks.md)
* [Contributing](docs/07-Contributing.md)
