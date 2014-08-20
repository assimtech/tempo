<?php

namespace Tempo;

use InvalidArgumentException;
use OutOfBoundsException;

class Definition
{
    /** @var \Tempo\Environment[] $environments */
    private $environments;

    /** @var \Tempo\Command[] $commands */
    private $commands;

    public function __construct()
    {
        $this->environments = array();
        $this->commands = array();
    }

    /**
     * @param \Tempo\Environment $environment
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
     * @param \Tempo\Environment[] $environments
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
     * @return \Tempo\Environment
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
     * @return \Tempo\Environment[]
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    /**
     * @param \Tempo\Command $command
     * @return self
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * @param \Tempo\Command[] $commands
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
     * @return \Tempo\Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
