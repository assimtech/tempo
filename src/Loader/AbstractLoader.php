<?php

namespace Assimtech\Tempo\Loader;

use RuntimeException;

abstract class AbstractLoader
{
    /**
     * @param string $path
     * @throws \RuntimeException
     */
    protected function validatePath($path)
    {
        if (!is_string($path)) {
            throw new RuntimeException(sprintf(
                '$path must be a string (%s given)',
                gettype($path)
            ));
        }

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'File not found: %s',
                $path
            ));
        }
    }

    /**
     * @param string $path
     * @return mixed
     */
    abstract public function load($path);
}
