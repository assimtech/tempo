<?php

namespace Tempo;

class Node
{
    /** @var string $host IP Address or hostname */
    private $host;

    /**
     * @param string $host IP Address or hostname
     */
    public function __construct($host)
    {
        $this->host = $host;
    }

    public function __toString()
    {
        return $this->host;
    }
}
