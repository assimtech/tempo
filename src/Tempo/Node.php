<?php

namespace Tempo;

class Node
{
    /** @var string $user The user to use for accessing this node */
    private $user;

    /** @var string $host IP Address or hostname */
    private $host;

    /**
     * @param string $host IP Address or hostname
     */
    public function __construct($user, $host)
    {
        $this->user = $user;
        $this->host = $host;
    }

    public function __toString()
    {
        return $this->user.'@'.$this->host;
    }
}
