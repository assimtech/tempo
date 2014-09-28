<?php

namespace Assimtech\Tempo;

use DomainException;

class Loader
{
    /**
     * @param string $dir the directory where your `tempo.php` resides, defaults to current working directory
     * @return \Assimtech\Tempo\Definition
     * @throws \DomainException
     */
    public static function loadTempoDefinition($dir = null)
    {
        if ($dir === null) {
            $dir = getcwd();
        }

        $tempoDefinitionPath = $dir . '/tempo.php';
        if (!file_exists($tempoDefinitionPath)) {
            throw new DomainException(sprintf(
                '%s is missing',
                $tempoDefinitionPath
            ));
        }
        $tempo = require $tempoDefinitionPath;
        if (!$tempo instanceof Definition) {
            throw new DomainException(sprintf(
                'Object returned by %s must be an instance of \Assimtech\Tempo\Definition',
                $tempoDefinitionPath
            ));
        }

        return $tempo;
    }
}
