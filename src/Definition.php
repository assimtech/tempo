<?php

namespace Assimtech\Tempo;

use Symfony\Component\Console\Command\Command;

class Definition
{
    /**
     * @var \Symfony\Component\Console\Command\Command[] $commands
     */
    private $commands;

    /**
     * @param \Symfony\Component\Console\Command\Command[] $commands
     */
    public function __construct(array $commands = array())
    {
        $this->commands = array();
        $this->addCommands($commands);
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @return self
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * @param \Symfony\Component\Console\Command\Command[] $commands
     * @return self
     */
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        return $this;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
