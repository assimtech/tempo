<?php

namespace Tempo\Node;

use Symfony\Component\Process\Process;
use RuntimeException;

class Remote extends AbstractNode
{
    /**
     * @param string|array $properties IP address or hostname or user@hostname or associative array of properties
     *
     * Built-in properties are:
     *      user - The user to use for the ssh connection
     *      host - The hostname or IP address
     *      controlMasterSocket - The socket file to use for connection sharing (see ControlPath in ssh_config(5))
     *      controlLifetime - The socket file to use for connection sharing (see ControlPersist in ssh_config(5))
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

        // Set some nice default options
        $defaultProperties = array(
            'controlLifetime' => '10m',
        );

        parent::__construct(array_merge($defaultProperties, $properties));
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $name = $this['host'];

        if (isset($this['user'])) {
            $name = sprintf(
                '%s@%s',
                $this['user'],
                $name
            );
        }

        return $name;
    }

    /**
     * @throws \RuntimeException
     */
    private function establishControlMaster()
    {
        // Default the controlMasterSocket to ~/.ssh/tempo_ctlmstr_<hash of $this>
        if (!isset($this['controlMasterSocket'])) {
            $this['controlMasterSocket'] = sprintf(
                '~/.ssh/tempo_ctlmstr_%s',
                md5(print_r((array)$this, true))
            );
        }

        // Check if control master socket already exists
        $returnVal = null;
        $checkCommand = sprintf(
            '[ -S %s ]',
            $this['controlMasterSocket']
        );
        system($checkCommand, $returnVal);
        if ($returnVal === 0) {
            return;
        }

        printf(
            "Establishing a connection to: %s\n",
            $this
        );
        $args = array(
            '-n', // Redirects stdin from /dev/null (actually, prevents reading from stdin).
            '-T', // Disable pseudo-tty allocation.
            '-M', // Places the ssh client into "master" mode for connection sharing.
            sprintf(
                '-S %s', // Specifies the location of a control socket for connection sharing
                escapeshellarg($this['controlMasterSocket'])
            ),
            sprintf(
                '-o %s', // ControlPersist - How long to persist the master socket for
                escapeshellarg('ControlPersist='.$this['controlLifetime'])
            )
        );
        if (isset($this['port'])) {
            $args[] = sprintf(
                '-p %d', // Specifies the location of a control socket for connection sharing
                $this['port']
            );
        }
        $controlCommand = sprintf(
            'ssh %s %s',
            implode(' ', $args),
            $this
        );
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
    public function run($commands)
    {
        $this->establishControlMaster();

        $process = new Process(sprintf(
            "ssh -S %s %s %s",
            $this['controlMasterSocket'],
            $this,
            escapeshellarg($commands)
        ));
        $process->setTimeout(null);
        $process->mustRun();

        return $process->getOutput();
    }
}
