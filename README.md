# tempo

[![Build Status](https://travis-ci.org/assimtech/tempo.svg?branch=master)](https://travis-ci.org/assimtech/tempo)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assimtech/tempo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assimtech/tempo/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assimtech/tempo/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assimtech/tempo/?branch=master)


Tempo is a scripting tool for running commands on local or remote nodes. It was originally developed to script complex
`php` project deployments however can be used for all kinds of tasks (e.g. unix user management, package updates etc).


## Quick start

Download [tempo.phar](https://github.com/assimtech/tempo/releases/download/0.2.1/tempo.phar).
If you place it in `~/bin/tempo` and make it executable you will be able to run `tempo` from any of your projects.

Alternatively, install tempo into your project with composer:

```shell
composer require assimtech/tempo
```

Create a `tempo.php` file in the root of your project containing the following:

```php
<?php

// If you have installed tempo with composer into your project, omit the autoloader
require_once __DIR__ . '/vendor/autoload.php';
// If you aren't using composer you are responsible for loading MyProject\Tempo\Command\* etc
// This can be done with require's or your own autoloader

use Assimtech\Tempo;
use MyProject\Tempo\Command;

// Infrastructure
$infrastructureLoader = Tempo\Factory\InfrastructureLoaderFactory::create();
$infrastructure = $infrastructureLoader->load(__DIR__ . '/infrastructure.yml');

// Commands
$definition = new Tempo\Definition();
foreach ($infrastructure->getEnvironments() as $env) {
    $definition->addCommand(new Command\WhereAmI($env));
}

return $definition;
```

Then create a `infrastructure.yml` file containing the following:
```yaml
nodes:
    server1:
        ssh:
            host: server1.example.com

environments:
    -
        name: test
        nodes: [ server1, server2 ]
```

Change "server1.example.com" to a server you have ssh access to.
If you need to change username / port etc, please see the documentation on how to setup a [Node](docs/04-Nodes.md)


Then create a `MyProject\Tempo\Command\WhereAmI` class containing the following:
```php
<?php

namespace MyProject\Tempo\Command;

use Assimtech\Tempo\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WhereAmI extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->env->getNodes() as $node) {
            $hostname = $node->run('hostname --fqdn');
            $output->writeln("I'm on: $hostname");

            $ips = $node->run('/sbin/ifconfig');
            $output->writeln($ips);
        }
        return 0;
    }
}
```

Run tempo from within the root of your project:

```shell
tempo test:whereami
```

Try adding more environments / servers / commands etc


## Documentation

* [Overview](docs/01-Overview.md)
* [Installation](docs/02-Installation.md)
* [Environments](docs/03-Environments.md)
* [Nodes](docs/04-Nodes.md)
* [Commands](docs/05-Commands.md)
* [Tasks](docs/06-Tasks.md)
* [Contributing](docs/07-Contributing.md)
