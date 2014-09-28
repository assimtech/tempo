<?php

namespace Assimtech\Tempo\Node;

use ArrayObject;

abstract class AbstractNode extends ArrayObject
{
    /**
     * @param string $command Command to run
     * @return string The command output
     */
    abstract public function run($command);

    /**
     * @return string
     */
    abstract public function __toString();
}
