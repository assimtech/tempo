<?php

namespace Tempo\Definition;

use Tempo\Tempo;
use DomainException;

class Loader
{
    /**
     * This is a static class
     */
    private function __construct()
    {
    }

    /**
     * @return \Tempo\Tempo
     * @throws \DomainException
     */
    public static function getTempo()
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
                'An instance of \Tempo\Tempo must be defined and returned in %s',
                $tempoDefinitionPath
            ));
        }

        if (!$tempo instanceof Tempo) {
            throw new DomainException(sprintf(
                'Object returned by %s must be an instance of \Tempo\Tempo',
                $tempoDefinitionPath
            ));
        }

        return $tempo;
    }
}
