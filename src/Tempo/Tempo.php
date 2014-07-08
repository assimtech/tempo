<?php

namespace Tempo;

use InvalidArgumentException;
use OutOfBoundsException;

class Tempo
{
    /** @var \Tempo\Environment[] $environments */
    private $environments;

    /** @var callable[] $strategies */
    private $strategies;

    public function __construct()
    {
        $this->environments = array();
        $this->strategies = array();
    }

    /**
     * @param \Tempo\Environment $environment
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
     * @param string $name
     * @return \Tempo\Environment
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
     * @param string $name
     * @param callable $strategy
     * @return self
     */
    public function addStrategy($name, $strategy)
    {
        if (isset($this->strategies[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Strategy: %s already exists',
                $name
            ));
        }

        $this->strategies[$name] = $strategy;

        return $this;
    }

    /**
     * @return \Tempo\Strategy
     */
    public function getStrategy($name)
    {
        return $this->strategies[$name];
    }
}
