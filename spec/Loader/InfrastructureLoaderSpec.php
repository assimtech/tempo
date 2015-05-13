<?php

namespace spec\Assimtech\Tempo\Loader;

use PhpSpec\ObjectBehavior;
use Assimtech\Tempo\Factory;
use Symfony\Component\Yaml;
use Assimtech\Tempo\Infrastructure;
use RuntimeException;

class InfrastructureLoaderSpec extends ObjectBehavior
{
    function let(Factory\InfrastructureFactory $infFactory,  Yaml\Parser $parser)
    {
        $this->beConstructedWith($infFactory, $parser);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Tempo\Loader\InfrastructureLoader');
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

    function it_can_load(Factory\InfrastructureFactory $infFactory,  Yaml\Parser $parser, Infrastructure $infrastructure)
    {
        $path = __DIR__.'/Stub/infrastructure.yml';
        $yaml = file_get_contents($path);
        $infConfig = array(
            'foo' => 'bar',
        );
        $parser->parse($yaml)->willReturn($infConfig);
        $infFactory->create($infConfig)->willReturn($infrastructure);
        $this->load($path)->shouldReturnAnInstanceOf('Assimtech\Tempo\Infrastructure');
    }
}
