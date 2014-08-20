<?php

namespace Tempo;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tempo\Environment;

class Command extends SymfonyCommand
{
    /** @var \Tempo\Environment $environment */
    protected $environment;

    /** @var callable $task */
    protected $task;

    /**
     * @param \Tempo\Environment $environment
     * @param string $taskName
     * @param callable $task Callable must have the signature: function (\Tempo\Environment $env)
     */
    public function __construct(Environment $environment, $taskName, $task)
    {
        $this->environment = $environment;
        $this->task = $task;

        $commandName = sprintf(
            '%s:%s',
            $environment,
            $taskName
        );

        parent::__construct($commandName);
    }

    /**
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        call_user_func($this->task, $this->environment);

        return 0;
    }
}
