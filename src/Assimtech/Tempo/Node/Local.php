<?php

namespace Assimtech\Tempo\Node;

use Symfony\Component\Process\Process;

class Local extends AbstractNode
{
    /**
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
