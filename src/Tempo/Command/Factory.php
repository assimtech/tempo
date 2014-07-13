<?php

namespace Tempo\Command;

use Tempo\Definition\Loader as DefinitionLoader;

class Factory
{
    public static function getCommands()
    {
        $commands = array();
        $tempo = DefinitionLoader::getTempo();

        foreach ($tempo->getEnvironments() as $environment) {
            $strategyNames = array_keys($environment->getStrategies());
            foreach ($strategyNames as $strategyName) {
                $commands[] = new Strategy($environment, $strategyName);
            }
        }

        return $commands;
    }
}
