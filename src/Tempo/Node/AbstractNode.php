<?php

namespace Tempo\Node;

use ArrayObject;
use InvalidArgumentException;

abstract class AbstractNode extends ArrayObject
{
    /**
     * Runs a task as specified by a given callable which returns the command string to run on the node
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
     * @param string $commands Command(s) to run
     * @return string The command output
     */
    abstract public function run($commands);
}
