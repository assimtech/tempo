<?php

namespace Assimtech\Tempo\Test\Loader;

use PHPUnit_Framework_TestCase;

class AbstractLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage $path must be a string (NULL given)
     */
    public function testStringValidation()
    {
        $path = null;

        $testLoader = new Mock\TestLoader();
        $testLoader->load($path);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegEx /^File not found: .+thisdoesnotexist$/
     */
    public function testFileDoesNotExist()
    {
        $path = __DIR__ . '/thisdoesnotexist';

        $testLoader = new Mock\TestLoader();
        $testLoader->load($path);
    }

    public function testFileExists()
    {
        $path = __FILE__;

        $testLoader = new Mock\TestLoader();
        $testLoader->load($path);
    }
}
