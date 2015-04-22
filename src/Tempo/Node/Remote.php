<?php

namespace Assimtech\Tempo\Node;

use InvalidArgumentException;
use Symfony\Component\Process\ProcessBuilder;

class Remote extends AbstractNode
{
    /**
     * @var \Symfony\Component\Process\ProcessBuilder $sshProcessBuilder
     */
    private $processBuilder;

    /**
     * @param string|array $properties IP address or hostname or user@hostname or associative array of properties
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
    public function __construct($properties)
    {
        // Handle string shortcut setup
        if (is_string($properties)) {
            $userHost = explode('@', $properties);
            if (count($userHost) === 2) {
                $properties = array(
                    'ssh' => array(
                        'user' => $userHost[0],
                        'host' => $userHost[1],
                    ),
                );
            } else {
                $properties = array(
                    'ssh' => array(
                        'host' => $properties,
                    ),
                );
            }
        }

        if (!is_array($properties)) {
            throw new InvalidArgumentException('properties must be either an array or string');
        }

        $properties = self::setupDefaults($properties);

        self::validateProperties($properties);

        parent::__construct($properties);
    }

    /**
     * @param array $properties
     * @return array
     */
    protected static function setupDefaults(array $properties)
    {

        if (!isset($properties['ssh']['options'])) {
            $properties['ssh']['options'] = array();
        }

        if (!isset($properties['ssh']['control'])) {
            $properties['ssh']['control'] = array();
        }

        $properties['ssh']['options'] = array_merge(array(
            'RequestTTY' => 'no', // Disable pseudo-tty allocation
        ), $properties['ssh']['options']);

        // Default control options
        $properties['ssh']['control'] = array_merge(array(
            'useControlMaster' => true,
        ), $properties['ssh']['control']);

        if ($properties['ssh']['control']['useControlMaster']) {
            $properties['ssh']['control'] = array_merge(array(
                'ControlPath' => '~/.ssh/tempo_' . $properties['ssh']['host'],
                'ControlPersist' => '5m', // We could set to yes but if they Ctl+C the command the socket may be left
                'closeOnDestruct' => false,
            ), $properties['ssh']['control']);
        }

        return $properties;
    }

    /**
     * @param array $properties
     * @throws \InvalidArgumentException
     */
    protected static function validateProperties(array $properties)
    {
        if (!isset($properties['ssh']['host']) || empty($properties['ssh']['host'])) {
            throw new InvalidArgumentException('property: [ssh][host] is mandatory');
        }

        foreach (array(
            'ControlPath',
            'ControlPersist',
        ) as $controlOption) {
            if (isset($properties['ssh']['options'][$controlOption])) {
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
    private function getSshOptionArgs()
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
        if ($this['ssh']['control']['useControlMaster']
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
            $string = $this['ssh']['user'].'@'.$string;
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

        return ($ret === 0);
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
        $process->mustRun();

        return $process->getOutput();
    }
}
