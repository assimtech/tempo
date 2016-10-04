<?php

namespace spec\Assimtech\Tempo\Node;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use InvalidArgumentException;
use Symfony\Component\Process;

class RemoteSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('test-host');
        $this->shouldHaveType('Assimtech\Tempo\Node\Remote');
    }

    function it_can_be_constructed_with_host()
    {
        $host = 'test-host';
        $this->beConstructedWith($host);
        $this->shouldHaveType('Assimtech\Tempo\Node\Remote');
        $this->__toString()->shouldReturn($host);
    }

    function it_can_be_constructed_with_user_host()
    {
        $userhost = 'test-user@test-host';
        $this->beConstructedWith($userhost);
        $this->shouldHaveType('Assimtech\Tempo\Node\Remote');
        $this->__toString()->shouldReturn($userhost);
    }

    function it_can_be_constructed_with_defaults()
    {
        $userhost = 'test-user@test-host';
        $this->beConstructedWith($userhost);
        $this->shouldHaveType('Assimtech\Tempo\Node\Remote');
        $this->getArrayCopy()->shouldBeLike(array(
            'ssh' => array(
                'host' => 'test-host',
                'options' => array(
                    'RequestTTY' => 'no',
                ),
                'control' => array(
                    'ControlPath' => '~/.ssh/tempo_ctl_%r@%h:%p',
                    'ControlPersist' => '5m',
                    'closeOnDestruct' => false,
                    'useControlMaster' => true,
                ),
                'user' => 'test-user',
            ),
        ));
    }

    function it_cant_be_constructed_without_host()
    {
        $this
            ->shouldThrow(new InvalidArgumentException('property: [ssh][host] is mandatory'))
            ->during('__construct')
        ;
    }

    function it_cant_be_constructed_with_control_options_in_options()
    {
        $this
            ->shouldThrow(new InvalidArgumentException(
                'The ssh option ControlPath can only be specified in the [ssh][control] section'
            ))
            ->during('__construct', array(
                array(
                    'ssh' => array(
                    'host' => 'test-host',
                    'options' => array(
                        'ControlPath' => 'foo',
                    ),
                ),
            )))
        ;
    }

    function it_can_close_master_on_destruct(Process\ProcessBuilder $processBuilder, Process\Process $process)
    {
        $this->beConstructedWith(array(
            'ssh' => array(
                'host' => 'test-host',
                'control' => array(
                    'useControlMaster' => true,
                    'closeOnDestruct' => true,
                ),
            ),
        ));

        $this->setProcessBuilder($processBuilder)->shouldReturn($this);

        // isControlMasterEstablished()
        $processBuilder->setArguments(array(
            '-O',
            'check',
            'test-host',
        ))->willReturn($processBuilder);
        $processBuilder->getProcess()->willReturn($process);
        $process->disableOutput()->willReturn($process);
        $process->run()->shouldBeCalled();
        $process->getExitCode()->willReturn(0);

        // close control master
        $processBuilder->setArguments(array(
            '-O',
            'exit',
            'test-host',
        ))->willReturn($processBuilder);
        $processBuilder->getProcess()->willReturn($process);
        $process->disableOutput()->willReturn($process);
        $process->mustRun()->shouldBeCalled();

        $this->__destruct();
    }

    function it_can_construct_a_process_builder()
    {
        $this->beConstructedWith('test-host');
        $this->getProcessBuilder()->shouldReturnAnInstanceOf('Symfony\Component\Process\ProcessBuilder');
    }

    function it_can_run(Process\ProcessBuilder $processBuilder, Process\Process $process)
    {
        $this->beConstructedWith(array(
            'ssh' => array(
                'host' => 'test-host',
                'options' => array(
                    'AddressFamily' => 'inet6',
                    'Port' => 2222,
                ),
            ),
        ));

        $command = 'test';
        $output = 'test-output';

        $this->setProcessBuilder($processBuilder)->shouldReturn($this);

        // isControlMasterEstablished()
        $processBuilder->setArguments(array(
            '-O',
            'check',
            'test-host',
        ))->willReturn($processBuilder);
        $processBuilder->getProcess()->willReturn($process);
        $process->disableOutput()->willReturn($process);
        $process->run()->shouldBeCalled();
        $process->getExitCode()->willReturn(1);

        // establishControlMaster()
        $processBuilder->setArguments(array(
            '-n',

            '-o',
            'ControlMaster=yes',

            '-o',
            'ControlPersist=5m',

            '-o',
            'RequestTTY=no',

            '-o',
            'AddressFamily=inet6',

            '-o',
            'Port=2222',

            'test-host',
        ))->willReturn($processBuilder);
        $processBuilder->getProcess()->willReturn($process);
        $process->disableOutput()->willReturn($process);
        $process->mustRun()->shouldBeCalled();

        // run()
        $processBuilder->setArguments(array(
            '-o',
            'RequestTTY=no',

            '-o',
            'AddressFamily=inet6',

            '-o',
            'Port=2222',

            'test-host',
        ))->willReturn($processBuilder);
        $processBuilder->setInput($command)->willReturn($processBuilder);
        $processBuilder->getProcess()->willReturn($process);
        $process->setTimeout(null)->shouldBeCalled();
        $process->mustRun()->shouldBeCalled();
        $process->getOutput()->willReturn($output);

        $this->run($command)->shouldReturn($output);
    }
}
