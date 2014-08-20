<?php

namespace Tempo\Node;

use Symfony\Component\Process\Process;
use RuntimeException;

class Remote extends AbstractNode
{
    /**
     * @param string $host IP Address or hostname
     * @param array $options Associative array of options
     *
     * Available options are:
     *      user - The user to use for the ssh connection
     *      controlMasterSocket - The socket file to use for connection sharing (see ControlPath in ssh_config(5))
     *      controlLifetime - The socket file to use for connection sharing (see ControlPersist in ssh_config(5))
     */
    public function __construct($array)
    {
        if (is_string($array)) {
            $array = array(
                'host' => $array,
            );
        }

        // Set some nice default options
        $array = array_merge(array(
            'controlMasterSocket' => sprintf(
                '~/.ssh/tempo_ctlmstr_%s',
                $array['host']
            ),
            'controlLifetime' => '10m',
        ), $array);

        parent::__construct($array);
    }

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

    private function establishControlMaster()
    {
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
