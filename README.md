# tempo

[![Build Status](https://travis-ci.org/kralos/tempo.svg?branch=master)](https://travis-ci.org/kralos/tempo)

Automated deployment for server side software

Tempo allows you express how to deploy your software to your servers using a few simple definitions.


## Installation

Tempo can either be installed as:

*   a library in your project
*   an executable [phar](http://www.php.net/manual/en/intro.phar.php)


## Set up

Firstly you will need to describe how you want your project deployed.  This is done by creating a `tempo.php` file in the root of your project.


### What we need define for tempo

#### Environments

An environment is a group of server(s) where your software may be deployed to.

Common examples include:

*   staging
*   testing
*   demo
*   production

To define an environment simply:

    // tempo.php

    $tempo = new Tempo\Tempo();
    $production = new Tempo\Environment('production');
    $tempo->addEnvironment($production);


#### Nodes

A node is a singular host / server (be it physical or virtual) where a single copy of your software is deployed.

A node can be defined by any host or IP address which is valid in the network you are deploying from.

    // tempo.php

    $tempo = new Tempo\Tempo();

    $production = new Tempo\Environment('production');
    $tempo->addEnvironment($production);

    $server = new Tempo\Node('example.com');
    $production->addNode($server);


#### Tasks

A Task is a PHP [callable](http://www.php.net/manual/en/language.types.callable.php) which achieves a singular goal.  We aim to include enough common tasks in tempo such that most people don't have to write one. However you can add your own tasks to tempo if you want to perform something specific (or send us a pull request if you think others might use your task).

E.g. A task might be defined as:

    /**
     * @var callable $rsync Copies files from one place to another
     * This task depends on:
     *      - shell
     *      - optbuilder (only required of options are supplied)
     *
     * @param string $origin The origin defined in user@host:[:] syntax
     * @param string $destination The destination defined in user@host:[:] syntax
     * @param array $options An array of valid rsync options confirming to the optbuilder format
     */
    $rsync = function ($origin, $destination, $options = array()) use ($tempo) {
        $cmd = 'rsync';
        if (!empty($options)) {
            $cmd .= ' ' . $tempo->runTask('optbuilder', $options);
        }
        $cmd .= " $origin $destination";

        return $tempo->runTask('shell', $cmd);
    }
    $tempo->addTask('rsync', $rsync, array(
        // Dependencies
        'shell',
        'optbuilder',
    ));


#### Strategies

A strategy is a method for deploying or performing common tasks with your software written as a PHP [callable](http://www.php.net/manual/en/language.types.callable.php) with the aid of [Tasks]

Common examples of strategies might be:

*   Disabling a web site before commencing deployment
*   Deploying software to all nodes for a given environment
*   Migrating a database to a new version
*   Enabling a web site after deployment

It's feasible that all of the above could be separate strategies or a singular strategy depending on your needs.

    // tempo.php

    $tempo = new Tempo\Tempo();

    $production = new Tempo\Environment('production');
    $tempo->addEnvironment($production);

    $server = new Tempo\Node('example.com');
    $production->addNode($server);

    $deploy = function () use ($tempo) {
        $frontEndProxy = $tempo->getNode('fep');
        $frontEndProxy->runTask('disable');

        $origin = __DIR__ . 'releaseBuilds/' . $tempo->getNewVersion();
        foreach ($tempo->getNodes('web') as $node) {
            $tempo->runTask('rsync', $origin, $node);
        }

        $database = $tempo->getNode('db');
        $database->runTask('migrate', $tempo->getOldVersion(), $tempo->getNewVersion());

        $frontEndProxy->runTask('enable');
    }


### Common example set ups

#### A single production server

    <?php

    $tempo = new Tempo\Tempo();

    $production = $tempo->addEnvironment('production');

    // You can use an IP address or fqdn etc
    $myProductionServer = $production->addNode('example.com');


#### A single server in multiple environments


#### Multiple servers in multiple environments


## Usage


