# tempo

[![Build Status](https://travis-ci.org/assimtech/tempo.svg?branch=master)](https://travis-ci.org/assimtech/tempo)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assimtech/tempo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assimtech/tempo/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assimtech/tempo/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assimtech/tempo/?branch=master)


A deployment tool for `php` projects. Execute commands on local and remote nodes using `php`.


## Quick start

Install tempo into your project with composer:

```shell
composer require assimtech/tempo
```

Create a `tempo.php` file in the root of your project containing the following:

```php
<?php

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
        nodes: [ server1 ]
```

Change "server1.example.com" to a server you have ssh access to.
If you need to change username / port etc, please see the documentation on how to setup a [Node](docs/04-Nodes.md)


Then create a `MyProject\Tempo\Command\WhereAmI` class containing the following:
```php
<?php

namespace MyProject\Tempo\Command;

use Assimtech\Sysexits;
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
            $output->write("<comment>Checking uname of $node: </comment>");
            $uname = $node->run('uname -a');
            $output->writeln("<info>$uname</info>");
        }

        return Sysexits::EX_OK;
    }
}
```

Run tempo from within the root of your project:

```shell
tempo test:whereami
```

Try adding more environments / servers / commands etc


## Known issues

### Running tempo from a docker container may cause connection problems

Due to an issue with the latest ssh version not playing nicely with overlayfs you may experience a connection sharing
issue like: `Control socket connect(...): Connection refused`

You may also notice this issue if script you are running seems to be authenticating again for each remote command or if
you see the MOTD coming back in the response for each command.

[https://bugs.launchpad.net/ubuntu/+source/linux/+bug/1262287](https://bugs.launchpad.net/ubuntu/+source/linux/+bug/1262287)

If you have this issue, you could specify your control master path as a standard filesystem location in your `infrastructure.yml`:

```yaml
nodes:
    server1:
        ssh:
            host: server1.example.com
            control:
                ControlPath: /tmp/%r@%h:%p

environments:
    -
        name: test
        nodes: [ server1 ]
```


## Documentation

* [Overview](docs/01-Overview.md)
* [Installation](docs/02-Installation.md)
* [Environments](docs/03-Environments.md)
* [Nodes](docs/04-Nodes.md)
* [Commands](docs/05-Commands.md)
* [Tasks](docs/06-Tasks.md)
* [Contributing](docs/07-Contributing.md)
