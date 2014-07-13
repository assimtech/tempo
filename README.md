# tempo

[![Build Status](https://travis-ci.org/kralos/tempo.svg?branch=master)](https://travis-ci.org/kralos/tempo)

Automated deployment for server side software

Tempo allows you to express how software is deployed to your servers using a few simple definitions.


## Installation


### With composer

Add a development dependency to your project's `composer.json`.  Generally you aren't likely to want tempo installed on your production nodes, only your development machine.

    {
        "require-dev": {
            "kralos/tempo": "dev-master"
        }
    }



## Set up

Firstly you will need to describe how you want your project deployed.  This is done by creating a `tempo.php` file in the root of your project.


### What we need to define for tempo


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


Optionally, a node can also be given a role or multiple roles when registered in an environment

    // tempo.php

    // ...

    // Give our node a single role
    $server = new Tempo\Node('example.com');
    $production->addNode($server, 'web');

    // Or many at once
    $production->addNode($server, array(
        'db',
        'cache',
    ));

    // example.com is now our web, db and cache server, we can later use this to do things to it in a Strategy


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

A strategy is a method for deploying or performing common tasks with your software written as a PHP [callable](http://www.php.net/manual/en/language.types.callable.php) with the aid of Tasks.

Common examples of strategies might be:

*   Disabling a web site before commencing deployment
*   Deploying software to all nodes for a given environment
*   Migrating a database to a new version
*   Enabling a web site after deployment

It's feasible that all of the above could be separate strategies or a singular strategy depending on your needs.

    // tempo.php

    $tempo = new Tempo\Tempo();

    $production = new Tempo\Environment('prod');
    $tempo->addEnvironment($production);

    $fepServer = new Tempo\Node('user', 'fep.example.com');
    $production->addNode($fepServer, 'fep');

    $webServer1 = new Tempo\Node('user', 'web1.example.com');
    $production->addNode($webServer1, 'web');

    $webServer2 = new Tempo\Node('user', 'web2.example.com');
    $production->addNode($webServer2, 'web');

    $dbServer = new Tempo\Node('user', 'db.example.com');
    $production->addNode($dbServer, 'db');

    $fepWebDbDeploy = function () use ($tempo) {
        $frontEndProxy = $tempo->getNode('fep');
        // This will be executed on the FEP
        $frontEndProxy->runTask('varnish-use', 'maintenance');

        $origin = __DIR__ . 'releaseBuilds/' . $tempo->getNewVersion();
        foreach ($tempo->getNodes('web') as $webNode) {
            // This will be executed locally (rsync from local to the web servers)
            $tempo->runTask('rsync', $origin, $webNode);
        }

        $database = $tempo->getNode('db');
        // This will be executed locally (rsync from local to the db server)
        $tempo->runTask('rsync', $origin, $database);
        // This will be executed on the DB server
        $database->runTask('migrate', $tempo->getOldVersion(), $tempo->getNewVersion());

        // This will be executed on the FEP
        $frontEndProxy->runTask('varnish-use', 'boot');
    }
    // Lets tell tempo we could deploy to production
    $tempo->addStrategy($fepWebDbDeploy, 'deploy', array(
        $production,
    ));


## Usage

    tempo <strategy name> [environment name] [additional options]

An environment name is not required if you have exactly one environment defined in tempo, otherwise you muse specify an environment.

Additional options may be required by your strategy.

A common use case would be to require a version number for deployment.  Some users may however prefer to be asked interactively for the version number (allowing a Task to get the latest version and prompt the user if they just want to deploy the latest or override with a specific version).

Given the strategy defined above you could deploy by running:

    tempo deploy prod
