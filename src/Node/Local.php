<?php

namespace Assimtech\Tempo\Node;

use ArrayObject;
use Symfony\Component\Process\Process;

class Local extends ArrayObject implements NodeInterface
{
    /**
     * {@inheritdoc}
     */
    public function run($command)
    {
        $process = new Process($command);

        $process
            ->setTimeout(null)
            ->mustRun()
        ;

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
