<?php

namespace Tempo;

use Symfony\Component\Process\Process;
use InvalidArgumentException;
use OutOfBoundsException;

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
     * Runs a task as specified by a given callable which returns the command string to run on the local host
     *
     * @param callable $task Command(s) to run
     * @param mixed $paramater,... Zero or more parameters to be passed to the task
     * @return string The command output
     * @throws \InvalidArgumentException
     */
    public function runTask()
    {
        $args = func_get_args();
        $task = array_shift($args);

        if (!is_callable($task)) {
            throw new InvalidArgumentException('$task must be a callable');
        }

        $commands = call_user_func_array($task, $args);

        return $this->run($commands);
    }

    /**
     * Runs a command as specified by a given string on the local host
     *
     * @param string $commands Command(s) to run
     * @return string The command output
     */
    public function run($commands)
    {
        $process = new Process($commands);
        $process->setTimeout(null);
        $process->mustRun();

        return $process->getOutput();
    }
}
