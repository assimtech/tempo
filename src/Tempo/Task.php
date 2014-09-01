<?php

namespace Tempo;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Task
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
}
