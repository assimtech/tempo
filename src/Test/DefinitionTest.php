<?php

namespace Assimtech\Tempo\Test;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Definition;
use Assimtech\Tempo\Environment;
use Symfony\Component\Console\Command\Command;

class DefinitionTest extends PHPUnit_Framework_TestCase
{
    public function configProvider()
    {
        return array(
            array(__DIR__.'/tempo.yml'),
            array(array(
                'nodes' => array(
                    'server1' => array(
                        'ssh' => array(
                            'host' => 'server1.example.com',
                        ),
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
                            'db' => array(
                                'server1',
                            ),
                        ),
                    ),
                ),
            )),
        );
    }

    /**
     * @dataProvider configProvider
     */
    public function testConstructFromConfig($config)
    {
        $tempo = new Definition($config);

        $this->assertCount(1, $tempo->getEnvironments());

        $testEnv = $tempo->getEnvironment('test');

        $this->assertEquals('test', (string)$testEnv);
        $this->assertCount(1, $testEnv->getNodes());

        $node = $testEnv->getNode();

        $this->assertEquals('server1.example.com', (string)$node);

        $webNodes = $testEnv->getNodes('web');

        $this->assertEquals(array($node), $webNodes);

        $dbNodes = $testEnv->getNodes('db');

        $this->assertEquals(array($node), $dbNodes);
    }

    public function testConstructFromConfigWithoutNodes()
    {
        $config = array(
            'environments' => array(
                array(
                    'name' => 'test',
                ),
            ),
        );

        $tempo = new Definition($config);

        $this->assertCount(1, $tempo->getEnvironments());

        $testEnv = $tempo->getEnvironment('test');

        $this->assertEquals('test', (string)$testEnv);

        $this->assertEmpty($testEnv->getNodes());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage config: [environments] is mandatory
     */
    public function testConstructFromConfigWithoutEnvironments()
    {
        $config = array(
        );

        new Definition($config);
    }

    public function testAddEnvironment()
    {
        $tempo = new Definition();

        $environmentName = 'test';

        $environment = new Environment($environmentName);
        $tempo->addEnvironment($environment);

        $this->assertEquals($environment, $tempo->getEnvironment($environmentName));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Environment: test already exists
     */
    public function testAddDuplicateEnvironment()
    {
        $tempo = new Definition();

        $environmentName = 'test';

        $environment1 = new Environment($environmentName);
        $tempo->addEnvironment($environment1);

        $environment2 = new Environment($environmentName);
        $tempo->addEnvironment($environment2);
    }

    public function testAddEnvironments()
    {
        $tempo = new Definition();

        $environmentName1 = 'dev';
        $environmentName2 = 'test';

        $dev = new Environment($environmentName1);
        $test = new Environment($environmentName2);

        $environments = array(
            $dev,
            $test,
        );
        $tempo->addEnvironments($environments);

        $this->assertEquals($environments, array_values($tempo->getEnvironments()));
    }
    
    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Environment: test doesn't exist
     */
    public function testUndefinedEnvironment()
    {
        $tempo = new Definition();

        $environmentName = 'test';

        $tempo->getEnvironment($environmentName);
    }

    public function testAddCommand()
    {
        $tempo = new Definition();

        $command = new Command('test:command');

        $tempo->addCommand($command);

        $this->assertContains($command, $tempo->getCommands());
    }

    public function testAddCommands()
    {
        $tempo = new Definition();

        $command1 = new Command('test:command1');
        $command2 = new Command('test:command2');

        $commands = array(
            $command1,
            $command2,
        );

        $tempo->addCommands($commands);

        $this->assertEquals($commands, $tempo->getCommands());
    }
}
