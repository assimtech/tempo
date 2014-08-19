<?php

namespace Tempo;

use ArrayObject;
use Symfony\Component\Process\Process;
use InvalidArgumentException;
use RuntimeException;

class Node extends ArrayObject
{
    /** @var string $host */
    private $host;

    /** @var array $options */
    private $options;

    /**
     * @param string $host IP Address or hostname
     * @param array $options Associative array of options
     *
     * Available options are:
     *      user - The user to use for the ssh connection
     *      controlMasterSocket - The socket file to use for connection sharing (see ControlPath in ssh_config(5))
     *      controlLifetime - The socket file to use for connection sharing (see ControlPersist in ssh_config(5))
     */
    public function __construct($host, $options = array())
    {
        $this->host = $host;
        $this->options = $options;

        // Set some nice default options
        $this->options = array_merge(array(
            'controlMasterSocket' => sprintf(
                '~/.ssh/tempo_ctlmstr_%s',
                $this
            ),
            'controlLifetime' => '10m',
        ), $this->options);
    }

    public function __toString()
    {
        if (isset($this->options['user'])) {
            return sprintf(
                '%s@%s',
                $this->options['user'],
                $this->host
            );
        } else {
            return $this->host;
        }
    }

    private function establishControlMaster()
    {
        $returnVal = null;
        $checkCommand = sprintf(
            '[ -S %s ]',
            $this->options['controlMasterSocket']
        );
        system($checkCommand, $returnVal);
        if ($returnVal === 0) {
            return;
        }

        printf(
            "Establishing a connection to: %s\n",
            $this
        );
        $controlCommand = sprintf(
            'ssh -nTM -S %s -o "ControlPersist=%s" %s',
            $this->options['controlMasterSocket'],
            '10m',
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
     * Runs a task as specified by a given callable which returns the command string to run on the node
     *
     * @param callable $task Command(s) to run
     * @param mixed $paramater,... Zero or more parameters to be passed to the task
     * @return string The command output
     * @throws \InvalidArgumentException
     */
    public function runTask()
    {
        $args = func_get_args();
        $task = array_shift($args);

        if (!is_callable($task)) {
            throw new InvalidArgumentException('$task must be a callable');
        }

        $commands = call_user_func_array($task, $args);

        return $this->run($commands);
    }

    /**
     * Runs a command as specified by a given string on the node
     *
     * @param string $commands Command(s) to run
     * @return string The command output
     */
    public function run($commands)
    {
        $this->establishControlMaster();

        $process = new Process(sprintf(
            "ssh -S %s %s %s",
            $this->options['controlMasterSocket'],
            $this,
            escapeshellarg($commands)
        ));
        $process->setTimeout(null);
        $process->mustRun();

        return $process->getOutput();
    }
}
