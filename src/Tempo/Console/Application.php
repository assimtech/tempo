<?php

namespace Tempo\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Tempo\Command;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('Tempo', 'dev');
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\DeployCommand();

        return $commands;
    }
}
