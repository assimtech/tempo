<?php

namespace Assimtech\Tempo\Test\Node;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Node\Remote;

class RemoteTest extends PHPUnit_Framework_TestCase
{
    private function getMockCheckEstablishedProcess($exitCode)
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
        new Remote('');
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
     * @expectedExceptionMessage The ssh option ControlPersist can only be specified in the ssh control section
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

        $mockProcess1 = $this->getMockCheckEstablishedProcess(0);

        $mockProcess2 = $this->getMock('Symfony\Component\Process\Process', array(
            'disableOutput',
            'mustRun',
        ), array(null));
        $mockProcess2
            ->expects($this->once())
            ->method('disableOutput')
            ->will($this->returnValue($mockProcess2))
        ;
        $mockProcess2
            ->expects($this->once())
            ->method('mustRun')
        ;

        $mockProcessBuilder = $this->getMock('Symfony\Component\Process\ProcessBuilder', array(
            'setArguments',
            'getProcess',
        ));
        $mockProcessBuilder
            ->expects($this->exactly(2))
            ->method('setArguments')
            ->withConsecutive(
                array(array(
                    '-O',
                    'check',
                    $host,
                )),
                array(array(
                    '-O',
                    'exit',
                    $host,
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

        $this->descopeRemote($host, $mockProcessBuilder);
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
            ),
        );
        $cmd = 'my test';
        $testOutput = 'hello tester';

        $mockProcess1 = $this->getMockCheckEstablishedProcess(1);

        $mockProcess2 = $this->getMock('Symfony\Component\Process\Process', array(
            'disableOutput',
            'mustRun',
        ), array(null));
        $mockProcess2
            ->expects($this->once())
            ->method('disableOutput')
            ->will($this->returnValue($mockProcess2))
        ;
        $mockProcess2
            ->expects($this->once())
            ->method('mustRun')
        ;

        $mockProcess3 = $this->getMock('Symfony\Component\Process\Process', array(
            'setTimeout',
            'mustRun',
            'getOutput',
        ), array(null));
        $mockProcess3
            ->expects($this->once())
            ->method('setTimeout')
            ->with($this->equalTo(null))
            ->will($this->returnValue($mockProcess2))
        ;
        $mockProcess3
            ->expects($this->once())
            ->method('mustRun')
        ;
        $mockProcess3
            ->expects($this->once())
            ->method('getOutput')
            ->willReturn($testOutput)
        ;

        $mockProcess4 = $this->getMockCheckEstablishedProcess(1);

        $mockProcessBuilder = $this->getMock('Symfony\Component\Process\ProcessBuilder', array(
            'setArguments',
            'getProcess',
        ));
        $mockProcessBuilder
            ->expects($this->exactly(4))
            ->method('setArguments')
            ->withConsecutive(
                array(array(
                    '-O',
                    'check',
                    $host,
                )),
                array(array(
                    '-n',
                    '-o',
                    'RequestTTY=no',
                    '-o',
                    'ControlMaster=yes',
                    '-o',
                    'ControlPersist=10m',
                    '-o',
                    'Port=1234',
                    $host,
                )),
                array(array(
                    '-o',
                    'Port=1234',
                    $host,
                )),
                array(array(
                    '-O',
                    'check',
                    $host,
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
