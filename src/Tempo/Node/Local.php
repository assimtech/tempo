<?php

namespace Tempo\Node;

use Symfony\Component\Process\Process;

class Local extends AbstractNode
{
    /**
     * Runs a command as specified by a given string on the local host
     *
     * {@inheritdoc}
     */
    public function run($command)
    {
        $process = new Process($command);
        $process->setTimeout(null);
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return 'localhost';
    }
}
