<?php

namespace Tempo;

use InvalidArgumentException;
use OutOfBoundsException;
use Symfony\Component\Process\Process;

class Tempo
{
    /** @var \Tempo\Environment[] $environments */
    private $environments;

    public function __construct()
    {
        $this->environments = array();
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
     * @param callable|string $task Command(s) to run
     * @param mixed $paramater,... Zero or more parameters to be passed to the task
     * @return string The command output
     */
    public function run()
    {
        $args = func_get_args();
        $task = array_shift($args);
        if (is_string($task)) {
            $commands = $task;
        } else {
            $commands = call_user_func_array($task, $args);
        }

        $process = new Process($commands);
        $process->mustRun();

        return $process->getOutput();
    }
}
