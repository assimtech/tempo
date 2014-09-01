<?php

namespace Tempo\Node;

use ArrayObject;

abstract class AbstractNode extends ArrayObject
{
    /**
     * @param string $commands Command(s) to run
     * @return string The command output
     */
    abstract public function run($commands);

    /**
     * @return string
     */
    abstract public function __toString();
}
