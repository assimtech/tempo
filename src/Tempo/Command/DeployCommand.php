<?php

namespace Tempo\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends BaseCommand
{
    protected function configure()
    {
        $help = <<<EOT
The <info>deploy</info> command reads the tempo.yml file from
the current directory, processes it, and attempts to deploy the project
to the nominated environment.
EOT;

        $this
            ->setName('deploy')
            ->setDescription('DEPLOOOY')
            ->setDefinition(
                array(
                    new InputOption('verbose', 'v', InputOption::VALUE_NONE, 'show more details'),
                )
            )
            ->setHelp($help)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($args = $input->getArgument('env')) {
            $output->writeln('<error>Invalid argument '.implode(' ', $args).'.');

            return 1;
        }

        return 0;
    }
}
