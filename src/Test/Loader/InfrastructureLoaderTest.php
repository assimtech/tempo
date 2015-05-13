<?php

namespace Assimtech\Tempo\Test\Loader;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Loader\InfrastructureLoader;
use Assimtech\Tempo\Infrastructure;

class InfrastructureLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testInfrastructure()
    {
        $path = __FILE__;

        $mockFactory = $this->getMockBuilder('Assimtech\Tempo\Factory\InfrastructureFactory')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'create',
            ))
            ->getMock()
        ;

        $config = array(
            'nodes' => array(
                'server1' => array(
                ),
            ),
            'environments' => array(
                array(
                    'name' => 'test',
                    'nodes' => array(
                        'server1',
                    ),
                    'roles' => array(
                        'web' => array(
                            'server1',
                        ),
                    ),
                ),
            ),
        );

        $expectedInfrastructure = new Infrastructure();
        $mockFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($config))
            ->will($this->returnValue($expectedInfrastructure))
        ;

        $mockYamlLoader = $this->getMock('Symfony\Component\Yaml\Parser');
        $mockYamlLoader
            ->expects($this->once())
            ->method('parse')
            ->with($this->equalTo(file_get_contents($path)))
            ->will($this->returnValue($config))
        ;

        $infrastructureLoader = new InfrastructureLoader($mockFactory, $mockYamlLoader);
        $infrastructure = $infrastructureLoader->load($path);

        $this->assertEquals($expectedInfrastructure, $infrastructure);
    }
}
