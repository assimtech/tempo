<?php

namespace Tempo\Test;

use PHPUnit_Framework_TestCase;
use Tempo\Environment;

class EnvironmentTest extends PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $environmentName = 'test';

        $environment = new Environment($environmentName);

        $this->assertEquals($environmentName, (string)$environment);
    }
}
