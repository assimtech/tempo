<?php

namespace Assimtech\Tempo\Test\Node;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Node\Remote;

class RemoteTest extends PHPUnit_Framework_TestCase
{
    private function getMockCheckControlMasterProcess($exitCode)
    {
        $mockProcess = $this->getMock('Symfony\Component\Process\Process', array(
            'disableOutput',
            'run',
            'getExitCode',
        ), array(null));
        $mockProcess
            ->expects($this->once())
            ->method('disableOutput')
            ->will($this->returnValue($mockProcess))
        ;
        $mockProcess
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue($exitCode))
        ;
        $mockProcess
            ->expects($this->once())
            ->method('getExitCode')
            ->will($this->returnValue($exitCode))
        ;

        return $mockProcess;
    }

    private function getMockEstablishControlMasterProcess()
    {
        $mockProcess = $this->getMock('Symfony\Component\Process\Process', array(
            'disableOutput',
            'mustRun',
        ), array(null));
        $mockProcess
            ->expects($this->once())
            ->method('disableOutput')
            ->will($this->returnValue($mockProcess))
        ;
        $mockProcess
            ->expects($this->once())
            ->method('mustRun')
        ;

        return $mockProcess;
    }

    private function getMockExecuteProcess($testOutput)
    {
        $mockProcess = $this->getMock('Symfony\Component\Process\Process', array(
            'setTimeout',
            'mustRun',
            'getOutput',
        ), array(null));
        $mockProcess
            ->expects($this->once())
            ->method('setTimeout')
            ->with($this->equalTo(null))
            ->will($this->returnValue($mockProcess))
        ;
        $mockProcess
            ->expects($this->once())
            ->method('mustRun')
        ;
        $mockProcess
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn($testOutput)
        ;
        return $mockProcess;
    }

    private function getMockExitControlMasterProcess()
    {
        $mockProcess = $this->getMock('Symfony\Component\Process\Process', array(
            'disableOutput',
            'mustRun',
        ), array(null));
        $mockProcess
            ->expects($this->once())
            ->method('disableOutput')
            ->will($this->returnValue($mockProcess))
        ;
        $mockProcess
            ->expects($this->once())
            ->method('mustRun')
        ;

        return $mockProcess;
    }

    public function testHost()
    {
        $host = 'localhost';

        $node = new Remote($host);

        $this->assertEquals($host, (string)$node);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage property: [ssh][host] is mandatory
     */
    public function testNoHost()
    {
        new Remote();
    }

    public function testUserAtHost()
    {
        $userAtHost = 'user@localhost';

        $node = new Remote($userAtHost);

        $this->assertEquals($userAtHost, (string)$node);
    }

    public function testUserHost()
    {
        $user = 'user';
        $host = 'localhost';

        $node = new Remote(array(
            'ssh' => array(
                'host' => $host,
                'user' => $user,
            ),
        ));

        $this->assertEquals("$user@$host", (string)$node);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The ssh option ControlPersist can only be specified in the [ssh][control] section
     */
    public function testControlOption()
    {
        $host = 'localhost';

        new Remote(array(
            'ssh' => array(
                'host' => $host,
                'options' => array(
                    'ControlPersist' => '1m',
                ),
            ),
        ));
    }

    private function descopeRemote($options, $processBuilder)
    {
        $node = new Remote($options);
        $node->setProcessBuilder($processBuilder);
    }

    public function testDestruct()
    {
        $host = 'localhost';

        $mockProcess1 = $this->getMockCheckControlMasterProcess(0);

        $mockProcess2 = $this->getMockExitControlMasterProcess();

        $mockProcessBuilder = $this->getMock('Symfony\Component\Process\ProcessBuilder', array(
            'setArguments',
            'getProcess',
        ));
        $mockProcessBuilder
            ->expects($this->exactly(2))
            ->method('setArguments')
            ->withConsecutive(
                // Check Control Master
                array(explode(
                    ' ',
                    '-O check '.$host
                )),
                // Close Control Master
                array(explode(
                    ' ',
                    '-O exit '.$host
                ))
            )
        ;
        $mockProcessBuilder
            ->expects($this->exactly(2))
            ->method('getProcess')
            ->will($this->onConsecutiveCalls(
                $mockProcess1,
                $mockProcess2
            ))
        ;

        $this->descopeRemote(array(
            'ssh' => array(
                'host' => $host,
                'control' => array(
                    'closeOnDestruct' => true,
                ),
            ),
        ), $mockProcessBuilder);
    }

    public function testGetProcessBuilder()
    {
        $host = 'localhost';
        $remote = new Remote(array(
            'ssh' => array(
                'host' => $host,
                'control' => array(
                    'ControlPath' => '/my/test/path',
                ),
            ),
        ));

        $processBuilder = $remote->getProcessBuilder();
        $process = $processBuilder->getProcess();

        $this->assertEquals("'ssh' '-o' 'ControlPath=/my/test/path'", $process->getCommandLine());
    }

    public function testEstablishMaster()
    {
        $host = 'localhost';
        $options = array(
            'ssh' => array(
                'host' => $host,
                'options' => array(
                    'Port' => 1234,
                ),
                'control' => array(
                    'closeOnDestruct' => true,
                ),
            ),
        );
        $cmd = 'my test';
        $testOutput = 'hello tester';

        $mockProcess1 = $this->getMockCheckControlMasterProcess(1);
        $mockProcess2 = $this->getMockEstablishControlMasterProcess();
        $mockProcess3 = $this->getMockExecuteProcess($testOutput);
        $mockProcess4 = $this->getMockCheckControlMasterProcess(1);

        $mockProcessBuilder = $this->getMock('Symfony\Component\Process\ProcessBuilder', array(
            'setArguments',
            'getProcess',
        ));
        $mockProcessBuilder
            ->expects($this->exactly(4))
            ->method('setArguments')
            ->withConsecutive(
                // Check Control Master
                array(explode(
                    ' ',
                    '-O check '.$host
                )),
                // Establish Control Master
                array(explode(
                    ' ',
                    '-n -o ControlMaster=yes -o ControlPersist=5m -o RequestTTY=no -o Port=1234 '.$host
                )),
                // Execute
                array(explode(
                    ' ',
                    '-o RequestTTY=no -o Port=1234 '.$host
                )),
                // Check Control Master
                array(explode(
                    ' ',
                    '-O check '.$host
                ))
            )
        ;
        $mockProcessBuilder
            ->expects($this->exactly(4))
            ->method('getProcess')
            ->will($this->onConsecutiveCalls(
                $mockProcess1,
                $mockProcess2,
                $mockProcess3,
                $mockProcess4
            ))
        ;

        $node = new Remote($options);
        $node->setProcessBuilder($mockProcessBuilder);
        $output = $node->run($cmd);

        $this->assertEquals($testOutput, $output);
    }
}
