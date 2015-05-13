<?php

namespace Assimtech\Tempo\Test\Loader\Mock;

use Assimtech\Tempo\Loader\AbstractLoader;

class TestLoader extends AbstractLoader
{
    public function load($path)
    {
        $this->validatePath($path);
    }
}
