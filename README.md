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


## Quick start

Create a `tempo.php` file in the root of your project containing:

    $tempo = new Tempo\Tempo();

    $myenv = new Tempo\Environment('myenv');
    $tempo->addEnvironment($myenv);

    $myenv
        ->addNode(new Tempo\Node('server1'))
        ->addNode(new Tempo\Node('server2'))
    ;

    // Lets tell tempo we could test on myenv
    $myenv->addStrategy('test', function (Tempo\Environment $env) use ($tempo) {
        $nodes = $env->getNodes();

        foreach ($nodes as $node) {
            echo $node->run('hostname');
        }

        echo $tempo->run('hostname');
    });

    return $tempo;

Then run the following command:

    bin/tempo myenv:test


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

    return $tempo;


#### Nodes

A node is a singular host / server (be it physical or virtual) where a single copy of your software is deployed.

A node can be defined by any host or IP address which is valid in the network you are deploying from.

    // tempo.php

    $tempo = new Tempo\Tempo();

    $production = new Tempo\Environment('production');
    $tempo->addEnvironment($production);

    $server = new Tempo\Node('example.com');
    $production->addNode($server);

    return $tempo;


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

    // ...


#### Tasks

A Task may be a string which is executed on a shell or a PHP [callable](http://www.php.net/manual/en/language.types.callable.php) which returns commands to run.  We aim to include enough common tasks in tempo such that most people don't have to write one. However you can add your own tasks to tempo if you want to perform something specific (or send us a pull request if you think others might use your task). See `tempo/src/Tempo/Task` for the built in tasks.

E.g. An rsync task might be defined as:

    /**
     * Rsync - Copies files from one place to another
     *
     * @param string $origin The origin defined in user@host:[:] syntax
     * @param string $destination The destination defined in user@host:[:] syntax
     * @param string $options Valid rsync options
     */
    return function ($origin, $destination, $options = null) use ($tempo) {
        $cmd = 'rsync';
        if ($options !== null) {
            $cmd .= ' ' . $options;
        }
        $cmd .= " $origin $destination";

        return $cmd;
    };


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

    $fepServer = new Tempo\Node('fep.example.com', array(
        'user' => 'fepguy',
    ));
    $production->addNode($fepServer, 'fep');

    $webServer1 = new Tempo\Node('web1.example.com');
    $production->addNode($webServer1, 'web');

    $webServer2 = new Tempo\Node('web2.example.com');
    $production->addNode($webServer2, 'web');

    $dbServer = new Tempo\Node('db.example.com');
    $production->addNode($dbServer, 'db');

    // Lets tell tempo we could deploy to production
    $production->addStrategy('deploy', function (Tempo\Environment $env) use ($tempo) {
        $frontEndProxy = $env->getNode('fep');
        // This will be executed on the FEP
        $frontEndProxy->runTask('varnish-use', 'maintenance');

        $origin = __DIR__ . 'releaseBuilds/' . $tempo->getNewVersion();
        foreach ($env->getNodes('web') as $webNode) {
            // This will be executed locally (rsync from local to the web servers)
            $tempo->runTask('rsync', $origin, $webNode);
        }

        $database = $env->getNode('db');
        // This will be executed locally (rsync from local to the db server)
        $tempo->runTask('rsync', $origin, $database);
        // This will be executed on the DB server
        $database->runTask('migrate', $tempo->getOldVersion(), $tempo->getNewVersion());

        // This will be executed on the FEP
        $frontEndProxy->runTask('varnish-use', 'boot');
    });

    return $tempo;


## Usage

    tempo <environment name>:<strategy name>

Given the strategy defined above you could deploy by running:

    tempo prod:deploy
