# Installation

## As a PHP Archive (PHAR) - This is the simplest way to use tempo

Download [tempo.phar]() and place wherever you want. If you place it in `~/bin/tempo` and make it executable you will be
able to run `tempo` from any of your projects (don't forget to restart your terminal if you just created `~/bin`).

You could also add it to a systemwide `bin` directory such as `/usr/local/bin/tempo`.


## With composer

If your project is already using composer, add a dependency to your project's `composer.json`.

    {
        "require": {
            "assimtech/tempo": "dev-master"
        }
    }

This will make tempo share your autoloader so you can reference any [Environments](03-Environments.md),
[Nodes](04-Nodes.md), [Commands](05-Commands.md) or [Tasks](06-Tasks.md) from either tempo's core or your project's
namespace without having to do any special loading in your `tempo.php`.


### As part of your existing symfony 2 console application

Since tempo exposes commands compatible with a `Symfony\Component\Console\Application`, it is possible to add your tempo
commands to your exiting Symfony Console application.  This would allow you to run tempo from your normal application's
entry point.

To achieve this, the best place to start is to have a read of `bin/tempo`.  The only part you need is:

    $tempo = Assimtech\Tempo\Loader::loadTempoDefinition();
    $application->addCommands($tempo->getCommands());

The loader simply loads the tempo definition from your project's `tempo.php`.
You can then get the `Symfony\Component\Console\Command\Command`'s from `$tempo`
and add them to your existing `Symfony\Component\Console\Application $application`.


## Use from source / git

This is how you will want to use tempo if you are contributing to it or want to use git to manage your tempo version.

    git clone git@github.com:assimtech/tempo.git
    cd tempo
    composer install

Then we would recommend creating a symbolic link to `tempo/bin/tempo` from your `~/bin` directory or a systemwide `bin`.

If you decide to checkout tags later on, (upgrade / downgrade), don't forget to `composer install` again.
