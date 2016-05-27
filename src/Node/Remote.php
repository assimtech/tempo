<?php

namespace Assimtech\Tempo\Node;

use Assimtech\Tempo\ArrayObject\ValidatableArrayObject;
use InvalidArgumentException;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Assimtech\Tempo\Process\Exception\RemoteProcessFailedException;
use Assimtech\Sysexits;

class Remote extends ValidatableArrayObject implements NodeInterface
{
    /**
     * @var \Symfony\Component\Process\ProcessBuilder $sshProcessBuilder
     */
    protected $processBuilder;

    /**
     * {@inheritdoc}
     *
     * Built-in properties are:
     *  [Mandatory]
     *  [ssh][host] - The hostname or IP address for the ssh connection
     *
     *  [Optional]
     *  [ssh][user] - The user to use for the ssh connection
     *
     *  [ssh][options] - An associative array of ssh options, see -o option in ssh(1)
     *
     *  [ssh][control][useControlMaster] - Use control master connection?
     *  [ssh][control][ControlPath] - see ControlPath in ssh_config(5)
     *  [ssh][control][ControlPersist] - see ControlPersist in ssh_config(5)
     *  [ssh][control][closeOnDestruct] - Should the control master connection be destroyed when this node is?
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($input = array(), $flags = 0, $iteratorClass = 'ArrayIterator')
    {
        // Handle string shortcut setup
        if (is_string($input)) {
            $userHost = explode('@', $input);
            if (count($userHost) === 2) {
                $input = array(
                    'ssh' => array(
                        'user' => $userHost[0],
                        'host' => $userHost[1],
                    ),
                );
            } else {
                $input = array(
                    'ssh' => array(
                        'host' => $input,
                    ),
                );
            }
        }

        // Defaults
        if (is_array($input)) {
            $input = array_replace_recursive(array(
                'ssh' => array(
                    'host' => null,
                    'options' => array(
                        'RequestTTY' => 'no', // Disable pseudo-tty allocation
                    ),
                    'control' => array(
                        'useControlMaster' => true,
                    ),
                ),
            ), $input);

            if ($input['ssh']['control']['useControlMaster']) {
                $input['ssh']['control'] = array_merge(array(
                    'ControlPath' => '~/.ssh/tempo_' . $input['ssh']['host'],
                    'ControlPersist' => '5m',
                    'closeOnDestruct' => false,
                ), $input['ssh']['control']);
            }
        }

        parent::__construct($input, $flags, $iteratorClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($index = null)
    {
        if ($index === null || $index === 'ssh') {
            $this->validateSsh();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validateSsh()
    {
        if (!isset($this['ssh']['host']) || empty($this['ssh']['host'])) {
            throw new InvalidArgumentException('property: [ssh][host] is mandatory');
        }

        foreach (array(
            'ControlPath',
            'ControlPersist',
        ) as $controlOption) {
            if (isset($this['ssh']['options'][$controlOption])) {
                throw new InvalidArgumentException(sprintf(
                    'The ssh option %s can only be specified in the [ssh][control] section',
                    $controlOption
                ));
            }
        }
    }

    /**
     * @return array
     */
    protected function getSshOptionArgs()
    {
        $args = array();

        foreach ($this['ssh']['options'] as $option => $value) {
            $args[] = '-o';
            $args[] = $option.'='.$value;
        }

        return $args;
    }

    /**
     * Destroy ControlMaster if nessasary
     * @throws \RuntimeException
     */
    public function __destruct()
    {
        if (isset($this['ssh'])
            && isset($this['ssh']['control'])
            && $this['ssh']['control']['useControlMaster']
            && $this['ssh']['control']['closeOnDestruct']
            && $this->isControlMasterEstablished()
        ) {
            $processBuilder = $this->getProcessBuilder();
            $processBuilder->setArguments(array(
                '-O', // Control an active connection multiplexing master process
                'exit',
                (string)$this
            ));
            $process = $processBuilder->getProcess();

            $process
                ->disableOutput()
                ->mustRun()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $string = $this['ssh']['host'];

        if (isset($this['ssh']['user'])) {
            $string = sprintf(
                '%s@%s',
                $this['ssh']['user'],
                $string
            );
        }

        return $string;
    }

    /**
     * @param \Symfony\Component\Process\ProcessBuilder $processBuilder
     * @return self
     */
    public function setProcessBuilder(ProcessBuilder $processBuilder)
    {
        $this->processBuilder = $processBuilder;

        return $this;
    }

    /**
     * @return \Symfony\Component\Process\ProcessBuilder
     */
    public function getProcessBuilder()
    {
        if ($this->processBuilder === null) {
            $this->processBuilder = new ProcessBuilder();

            $processPrefix = array(
                'ssh',
            );
            if ($this['ssh']['control']['useControlMaster']) {
                $processPrefix[] = '-o';
                $processPrefix[] = 'ControlPath='.$this['ssh']['control']['ControlPath'];
            }
            $this->processBuilder->setPrefix($processPrefix);
        }

        return $this->processBuilder;
    }

    protected function isControlMasterEstablished()
    {
        $processBuilder = $this->getProcessBuilder();
        $processBuilder->setArguments(array(
            '-O', // Control an active connection multiplexing master process
            'check',
            (string)$this
        ));
        $process = $processBuilder->getProcess();

        $process
            ->disableOutput()
            ->run()
        ;

        $ret = $process->getExitCode();

        return ($ret === Sysexits::EX_OK);
    }

    /**
     * @throws \RuntimeException
     */
    protected function establishControlMaster()
    {
        $processBuilder = $this->getProcessBuilder();
        $args = array_merge(array(
            '-n', // Redirects stdin from /dev/null (actually, prevents reading from stdin)

            '-o',
            'ControlMaster=yes',

            '-o', // ControlPersist - How to persist the master socket
            'ControlPersist='.$this['ssh']['control']['ControlPersist'],
        ), $this->getSshOptionArgs());
        $args[] = (string)$this;
        $processBuilder->setArguments($args);
        $process = $processBuilder->getProcess();

        $process
            ->disableOutput()
            ->mustRun()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function run($command)
    {
        if ($this['ssh']['control']['useControlMaster'] && !$this->isControlMasterEstablished()) {
            $this->establishControlMaster();
        }

        $processBuilder = $this->getProcessBuilder();
        $args = $this->getSshOptionArgs();
        $args[] = (string)$this;
        $processBuilder->setArguments($args);
        $processBuilder
            ->setInput($command)
        ;
        $process = $processBuilder->getProcess();

        $process->setTimeout(null);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            if ($process->getExitCode() !== 255) {
                // Rebuild the exception to expose actual failed command routed through ssh
                $process = $e->getProcess();
                throw new RemoteProcessFailedException($process);
            }

            throw $e;
        }

        return $process->getOutput();
    }
}
