<?php

namespace Assimtech\Tempo\Task;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTask
{
    /** @var \Symfony\Component\Console\Input\InputInterface $input */
    protected $input;

    /** @var \Symfony\Component\Console\Output\OutputInterface $output */
    protected $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Perform the task
     */
    abstract public function run();
}
