<?php

namespace Assimtech\Tempo\Node;

use Symfony\Component\Process\Process;
use InvalidArgumentException;
use RuntimeException;

class Remote extends AbstractNode
{
    /**
     * @param string|array $properties IP address or hostname or user@hostname or associative array of properties
     *
     * Built-in properties are:
     *  [Mandatory]
     *  host - The hostname or IP address
     *
     *  [Optional]
     *  user - The user to use for the ssh connection
     *  port - The ssh port to use when connecting
     *  useControlMaster - Use control master connection?
     *  controlPath - The socket file to use for connection sharing (see ControlPath in ssh_config(5))
     *  controlPersist - The policy for leaving the connection open (see ControlPersist in ssh_config(5))
     *  closeControlMasterOnDestruct - Should the control master connection be destroyed when this node is?
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($properties)
    {
        if (is_string($properties)) {
            $userHost = explode('@', $properties);
            if (count($userHost) === 2) {
                $properties = array(
                    'user' => $userHost[0],
                    'host' => $userHost[1],
                );
            } else {
                $properties = array(
                    'host' => $properties,
                );
            }
        }

        if (!isset($properties['host']) || empty($properties['host'])) {
            throw new InvalidArgumentException('host is mandatory');
        }

        // Set some nice default options
        $properties = array_merge(array(
            'useControlMaster' => true,
        ), $properties);

        // If using ControlMaster set up default options
        if ($properties['useControlMaster']) {
            $properties = array_merge(array(
                'controlPath' => sprintf(
                    '~/.ssh/tempo_ctlmstr_%s',
                    md5(print_r($properties, true))
                ),
                'controlPersist' => 'yes',
                'closeControlMasterOnDestruct' => true,
            ), $properties);
        }

        parent::__construct($properties);
    }

    /**
     * Destroy ControlMaster if nessasary
     * @throws \RuntimeException
     */
    public function __destruct()
    {
        if ($this['useControlMaster']
            && $this['closeControlMasterOnDestruct']
            && $this->isControlMasterEstablished()
        ) {
            $closeCommand = sprintf(
                'ssh -S %s -O exit %s',
                escapeshellarg($this['controlPath']),
                $this
            );
            $returnVal = null;

            passthru($closeCommand, $returnVal);

            if ($returnVal !== 0) {
                throw new RuntimeException(sprintf(
                    'could not close control connection using command: %s',
                    $closeCommand
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (isset($this['user'])) {
            return sprintf(
                '%s@%s',
                $this['user'],
                $this['host']
            );
        }

        return $this['host'];
    }

    private function isControlMasterEstablished()
    {
        // Check if control master socket already exists
        $returnVal = null;
        $checkCommand = sprintf(
            '[ -S %s ]',
            $this['controlPath']
        );

        system($checkCommand, $returnVal);

        return ($returnVal === 0);
    }

    /**
     * @throws \RuntimeException
     */
    private function establishControlMaster()
    {
        $args = array(
            '-n', // Redirects stdin from /dev/null (actually, prevents reading from stdin).
            '-T', // Disable pseudo-tty allocation.
            '-M', // Places the ssh client into "master" mode for connection sharing.
            sprintf(
                '-S %s', // Specifies the location of a control socket for connection sharing
                escapeshellarg($this['controlPath'])
            ),
            sprintf(
                '-o %s', // ControlPersist - How to persist the master socket
                escapeshellarg('ControlPersist='.$this['controlPersist'])
            )
        );
        if (isset($this['port'])) {
            $args[] = sprintf(
                '-p %d', // The ssh port
                escapeshellarg($this['port'])
            );
        }

        $controlCommand = sprintf(
            'ssh %s %s',
            implode(' ', $args),
            $this
        );
        $returnVal = null;

        passthru($controlCommand, $returnVal);

        if ($returnVal !== 0) {
            throw new RuntimeException(sprintf(
                'could not establish control connection using command: %s',
                $controlCommand
            ));
        }
    }

    /**
     * Runs a command as specified by a given string on the node
     *
     * {@inheritdoc}
     */
    public function run($command)
    {
        if ($this['useControlMaster'] && !$this->isControlMasterEstablished()) {
            $this->establishControlMaster();
        }

        $args = array();
        if ($this['useControlMaster']) {
            $args[] = sprintf(
                '-S %s',
                escapeshellarg($this['controlPath'])
            );
        }
        if (isset($this['port'])) {
            $args[] = sprintf(
                '-p %d', // The ssh port
                escapeshellarg($this['port'])
            );
        }

        $process = new Process(sprintf(
            'ssh %s %s %s',
            implode(' ', $args),
            $this,
            escapeshellarg($command)
        ));
        $process->setTimeout(null);
        $process->mustRun();

        return $process->getOutput();
    }
}
