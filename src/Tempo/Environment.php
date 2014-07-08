<?php

namespace Tempo;

class Environment
{
    /** @var string $name Environment name, typically one of: development, staging, testing, demo, production */
    private $name;

    /**
     * @var string $name Environment name, typically one of: development, staging, testing, demo, production
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
