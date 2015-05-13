<?php

namespace spec\Assimtech\Tempo\Loader;

use PhpSpec\ObjectBehavior;
use RuntimeException;

class DefinitionLoaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Loader\DefinitionLoader');
    }

    function it_cant_load_invalid_path()
    {
        $path = false;
        $this->shouldThrow(new RuntimeException("\$path must be a string (boolean given)"))->during('load', array(
            $path,
        ));
    }

    function it_cant_load_nonexistant_path()
    {
        $path = 'thisdoesntexist';
        $this->shouldThrow(new RuntimeException("File not found: $path"))->during('load', array(
            $path,
        ));
    }

    function it_cant_load()
    {
        $path = __DIR__.'/Stub/notdefinition.php';
        $this->shouldThrow(new RuntimeException("$path must return an instance of \Assimtech\Tempo\Definition (boolean returned)"))->during('load', array(
            $path,
        ));
    }

    function it_can_load()
    {
        $path = __DIR__.'/Stub/definition.php';
        $this->load($path)->shouldReturnAnInstanceOf('Assimtech\Tempo\Definition');
    }
}
