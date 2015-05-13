<?php

namespace Assimtech\Tempo\Test\Loader;

use PHPUnit_Framework_TestCase;
use Assimtech\Tempo\Loader\DefinitionLoader;

class DefinitionLoaderTest extends PHPUnit_Framework_TestCase
{
    public function testDefinition()
    {
        $path = __DIR__.'/Mock/WithDefinition/definition.php';

        $definitionLoader = new DefinitionLoader();
        $tempo = $definitionLoader->load($path);

        $this->assertInstanceOf('Assimtech\Tempo\Definition', $tempo);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /^File not found: .+thisdoesnotexist$/
     */
    public function testWithoutDefinition()
    {
        $path = __DIR__.'/thisdoesnotexist';

        $definitionLoader = new DefinitionLoader();
        $definitionLoader->load($path);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage definition.php must return an instance of \Assimtech\Tempo\Definition (array returned)
     */
    public function testWithInvalidDefinition()
    {
        $path = __DIR__.'/Mock/WithInvalidDefinition/definition.php';

        $definitionLoader = new DefinitionLoader();
        $definitionLoader->load($path);
    }
}
