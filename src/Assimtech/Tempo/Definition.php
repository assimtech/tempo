<?php

namespace Assimtech\Tempo;

use InvalidArgumentException;
use OutOfBoundsException;
use Symfony\Component\Console\Command\Command;

class Definition
{
    /**
     * @var \Assimtech\Tempo\Environment[] $environments
     */
    private $environments;

    /**
     * @var \Symfony\Component\Console\Command\Command[] $commands
     */
    private $commands;

    public function __construct()
    {
        $this->environments = array();
        $this->commands = array();
    }

    /**
     * @param \Assimtech\Tempo\Environment $environment
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addEnvironment(Environment $environment)
    {
        if (isset($this->environments[(string)$environment])) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s already exists',
                $environment
            ));
        }

        $this->environments[(string)$environment] = $environment;

        return $this;
    }

    /**
     * @param \Assimtech\Tempo\Environment[] $environments
     * @return self
     */
    public function addEnvironments($environments)
    {
        foreach ($environments as $environment) {
            $this->addEnvironment($environment);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return \Assimtech\Tempo\Environment
     */
    public function getEnvironment($name)
    {
        if (!isset($this->environments[$name])) {
            throw new OutOfBoundsException(sprintf(
                'Environment: %s doesn\'t exist',
                $name
            ));
        }

        return $this->environments[$name];
    }

    /**
     * @return \Assimtech\Tempo\Environment[]
     */
    public function getEnvironments()
    {
        return $this->environments;
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
    public function addCommands($commands)
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
