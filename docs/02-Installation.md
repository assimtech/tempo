# Installation

## As a PHP Archive (PHAR) - This is the simplest way to use tempo

Download [tempo.phar](https://github.com/assimtech/tempo/releases/download/0.2.0/tempo.phar) and place where you want.
If you place it in `~/bin/tempo` and make it executable you will be able to run `tempo` from any of your projects. Don't
forget to restart your terminal if you just created `~/bin`.

You could also add it to a systemwide `bin` directory such as `/usr/local/bin/tempo`.


## With composer

If your project is already using composer, add a dependency to your project's `composer.json`.

```shell
composer require assimtech/tempo
```

This will make tempo share your autoloader so you can reference any namespaces available within your project.


### As part of your existing Symfony 2 console application

Since tempo exposes commands compatible with a `Symfony\Component\Console\Application`, it is possible to add your tempo
commands to your exiting Symfony Console application.  This would allow you to run tempo from your normal application's
entry point.

To achieve this, the best place to start is to have a read of `bin/tempo`.  You simply need to add your tempo commands
to your symfony application:

```php
/** @var Assimtech\Tempo\Definition $definition */
$application->addCommands($definition->getCommands());
```


## Use from source / git

This is how you will want to use tempo if you are contributing to it or want to use git to manage your tempo version.

```shell
git clone git@github.com:assimtech/tempo.git
cd tempo
composer install
```

Then we would recommend creating a symbolic link to `bin/tempo` from your `~/bin` directory or a systemwide `bin`.

If you decide to checkout tags later on, (upgrade / downgrade), don't forget to `composer install` again.
