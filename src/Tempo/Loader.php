<?php

namespace Tempo;

use DomainException;

class Loader
{
    /**
     * @return \Tempo\Definition
     * @throws \DomainException
     */
    public static function loadTempoDefinition()
    {
        $cwd = getcwd();
        $tempoDefinitionPath = $cwd . '/tempo.php';
        if (!file_exists($tempoDefinitionPath)) {
            throw new DomainException(sprintf(
                '%s is missing',
                $tempoDefinitionPath
            ));
        }
        $tempo = require $tempoDefinitionPath;
        if (!$tempo) {
            throw new DomainException(sprintf(
                'An instance of \Tempo\Definition must be defined and returned in %s',
                $tempoDefinitionPath
            ));
        }
        if (!$tempo instanceof Definition) {
            throw new DomainException(sprintf(
                'Object returned by %s must be an instance of \Tempo\Definition',
                $tempoDefinitionPath
            ));
        }

        return $tempo;
    }
}
