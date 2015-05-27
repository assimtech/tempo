<?php

namespace Assimtech\Tempo;

use InvalidArgumentException;
use OutOfBoundsException;

class Infrastructure
{
    /**
     * @var \Assimtech\Tempo\Environment[] $environments
     */
    private $environments;

    public function __construct()
    {
        $this->environments = array();
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
}
