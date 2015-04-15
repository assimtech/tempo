<?php

namespace Assimtech\Tempo;

use InvalidArgumentException;
use OutOfBoundsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Parser;

class Definition
{
    /**
     * @var \Assimtech\Tempo\Environment[] $environments
     */
    private $environments;

    /**
     * @var \Symfony\Component\Console\Command\Command[] $commands
     */
    private $commands;

    /**
     * @param string|array|null $config Path to a tempo.yml file or config array or null for manual setup
     */
    public function __construct($config = null)
    {
        $this->environments = array();
        $this->commands = array();

        if ($config === null) {
            $config = array(
                'nodes' => array(),
                'environments' => array(),
            );
        }

        if (is_string($config)) {
            $yaml = new Parser();
            $config = $yaml->parse(file_get_contents($config));
        }

        $environments = $this->getEnvironmentsFromConfig($config);

        $this->addEnvironments($environments);
    }

    /**
     * @param string|array|null $config Path to a config file or array of config or null for manual setup
     * @return \Assimtech\Tempo\Node\AbstractNode[]
     */
    protected function getNodesFromConfig($config)
    {
        $nodes = array();

        if (!isset($config['nodes'])) {
            return $nodes;
        }

        foreach ($config['nodes'] as $nodeName => $nodeConfig) {
            $nodes[$nodeName] = new Node\Remote($nodeConfig);
        }

        return $nodes;
    }

    /**
     * @param string|array|null $config Path to a config file or array of config or null for manual setup
     * @return \Assimtech\Tempo\Environment[]
     */
    protected function getEnvironmentsFromConfig($config)
    {
        if (!isset($config['environments'])) {
            throw new InvalidArgumentException('config: [environments] is mandatory');
        }

        $nodes = $this->getNodesFromConfig($config);

        $environments = array();
        if (isset($config['environments'])) {
            foreach ($config['environments'] as $environmentConfig) {
                if (isset($environmentConfig['nodes'])) {
                    foreach ($environmentConfig['nodes'] as $i => $nodeName) {
                        $environmentConfig['nodes'][$i] = $nodes[$nodeName];
                    }
                }
                if (isset($environmentConfig['roles'])) {
                    foreach ($environmentConfig['roles'] as $role => $nodeNames) {
                        foreach ($nodeNames as $i => $nodeName) {
                            $environmentConfig['roles'][$role][$i] = $nodes[$nodeName];
                        }
                    }
                }
                $environments[] = new Environment($environmentConfig);
            }
        }

        return $environments;
    }

    /**
     * @param \Assimtech\Tempo\Environment $environment
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addEnvironment(Environment $environment)
    {
        if (isset($this->environments[(string)$environment])) {
            throw new InvalidArgumentException(sprintf(
                'Environment: %s already exists',
                $environment
            ));
        }

        $this->environments[(string)$environment] = $environment;

        return $this;
    }

    /**
     * @param \Assimtech\Tempo\Environment[] $environments
     * @return self
     */
    public function addEnvironments($environments)
    {
        foreach ($environments as $environment) {
            $this->addEnvironment($environment);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return \Assimtech\Tempo\Environment
     */
    public function getEnvironment($name)
    {
        if (!isset($this->environments[$name])) {
            throw new OutOfBoundsException(sprintf(
                'Environment: %s doesn\'t exist',
                $name
            ));
        }

        return $this->environments[$name];
    }

    /**
     * @return \Assimtech\Tempo\Environment[]
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @return self
     */
    public function addCommand(Command $command)
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * @param \Symfony\Component\Console\Command\Command[] $commands
     * @return self
     */
    public function addCommands($commands)
    {
        foreach ($commands as $command) {
            $this->addCommand($command);
        }

        return $this;
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getCommands()
    {
        return $this->commands;
    }
}
