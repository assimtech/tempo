<?php

namespace Tempo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tempo\Environment;

class Strategy extends Command
{
    /** @var \Tempo\Environment $environment */
    protected $environment;

    /** @var callable $strategy */
    protected $strategy;

    /**
     * @param \Tempo\Environment $environment
     * @param string $strategyName
     */
    public function __construct(Environment $environment, $strategyName)
    {
        $this->environment = $environment;
        $this->strategy = $environment->getStrategy($strategyName);

        $commandName = sprintf(
            '%s:%s',
            $environment,
            $strategyName
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
        call_user_func($this->strategy, $this->environment);

        return 0;
    }
}
