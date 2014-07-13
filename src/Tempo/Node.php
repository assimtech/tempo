<?php

namespace Tempo;

use RuntimeException;
use Symfony\Component\Process\Process;

class Node
{
    /** @var string $host IP Address or hostname */
    private $host;

    /** @var string $user The user to use for accessing this node */
    private $user;

    /**
     * @param string $host IP Address or hostname
     * @param string $user User for accessing the node (Optional, if not specified the current user will be used)
     */
    public function __construct($host, $user = null)
    {
        $this->host = $host;
        $this->user = $user;
    }

    public function __toString()
    {
        if ($this->user === null) {
            return $this->host;
        } else {
            return $this->user.'@'.$this->host;
        }
    }

    /**
     * @param callable|string $task Command(s) to run
     * @param mixed $paramater,... Zero or more parameters to be passed to the task
     * @return mixed
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

        $process = new Process("ssh $this");
        $process
            ->setInput($commands)
            ->mustRun()
        ;
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $process->getOutput();
    }
}
