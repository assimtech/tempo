<?php

namespace Assimtech\Tempo\Node;

interface NodeInterface
{
    /**
     * @param string $command Command to run
     * @return string The command output
     */
    public function run($command);

    /**
     * @return string
     */
    public function __toString();
}
