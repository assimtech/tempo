<?php

namespace Assimtech\Tempo\Loader;

use Assimtech\Tempo\Definition;
use RuntimeException;

class DefinitionLoader extends AbstractLoader
{
    /**
     * @param string $path The path to a php file that constructs and returns an \Assimtech\Tempo\Definition
     *      defaults to 'tempo/definition.php'
     * @return \Assimtech\Tempo\Definition
     * @throws \RuntimeException
     */
    public function load($path)
    {
        $this->validatePath($path);

        $definition = require $path;

        if (!$definition instanceof Definition) {
            throw new RuntimeException(sprintf(
                "%s must return an instance of \Assimtech\Tempo\Definition (%s returned)",
                $path,
                is_object($definition) ? get_class($definition) : gettype($definition)
            ));
        }

        return $definition;
    }
}
