<?php

namespace Assimtech\Tempo\Test;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Loader;

class LoaderTest extends PHPUnit_Framework_TestCase
{
    private $cwd;

    public function setUp()
    {
        parent::setUp();

        $this->cwd = getcwd();
    }

    public function tearDown()
    {
        chdir($this->cwd);
    }

    public function testDefinition()
    {
        $cwd = __DIR__.'/Loader/WithDefinition';
        chdir($cwd);

        $tempo = Loader::loadTempoDefinition();

        $this->assertInstanceOf('Assimtech\Tempo\Definition', $tempo);
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage tempo.php is missing
     */
    public function testWithoutDefinition()
    {
        $cwd = __DIR__.'/Loader/WithoutDefinition';
        chdir($cwd);

        Loader::loadTempoDefinition();
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Object returned by
     * @expectedExceptionMessage tempo.php must be an instance of \Assimtech\Tempo\Definition
     */
    public function testWithInvalidDefinition()
    {
        $cwd = __DIR__.'/Loader/WithInvalidDefinition';
        chdir($cwd);

        Loader::loadTempoDefinition();
    }
}
