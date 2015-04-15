<?php

namespace Assimtech\Tempo;

use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var \Assimtech\Tempo\Environment $env
     */
    protected $env;

    /**
     * {@inheritdoc}
     * @param \Assimtech\Tempo\Environment $env
     *
     * The environment name will be prefixed to the command name, e.g.
     *
     *      new MyCommand(
     *          new \Assimtech\Tempo\Environment('staging'),
     *          'deploy'
     *      );
     *
     * would result in a command:
     *
     *      tempo staging:deploy
     *
     * if $name is ommited, it will default to the class name of the command, e.g.
     *
     *      new MyCommand(
     *          new \Assimtech\Tempo\Environment('staging')
     *      );
     *
     * would result in a command:
     *
     *      tempo staging:mycommand
     *
     */
    public function __construct(Environment $env, $name = null)
    {
        $this->env = $env;

        if ($name === null) {
            $components = explode('\\', get_called_class());
            $class = array_pop($components);
            $name = strtolower($class);
        }

        parent::__construct(sprintf('%s:%s', $env, $name));
    }
}
