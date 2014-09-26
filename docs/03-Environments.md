# Environments

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
