<?php

namespace Assimtech\Tempo\Phar;

use Assimtech\Tempo\Node\Local;
use Symfony\Component\Finder\Finder;
use Phar;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;

final class Compiler
{
    /**
     * @var string $pharFile
     */
    private $pharFile;

    /**
     * @var string $baseDir
     */
    private $baseDir;

    /**
     * @var \Assimtech\Tempo\Node\Local $local
     */
    private $local;

    public function __construct()
    {
        $this->pharFile = 'tempo.phar';
        $this->baseDir = realpath(__DIR__.'/../../../../');
        $this->local = new Local();
    }

    /**
     * @throws \RuntimeException
     */
    public function compile()
    {
        $this->checkWorkingDirectory();

        $this->checkVersion();

        if (file_exists($this->pharFile)) {
            unlink($this->pharFile);
        }

        $phar = new Phar($this->pharFile);
        $phar->setSignatureAlgorithm(Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->exclude('Test')
            ->in('vendor/composer')
            ->in('vendor/symfony/console')
            ->in('vendor/symfony/process')
            ->in('src')
        ;
        foreach ($finder as $file) {
            $phar->addFile($file);
        }
        $phar->addFile('vendor/autoload.php');

        // Add bin/tempo but without shebang
        $tempoBinContents = file_get_contents($this->baseDir.'/bin/tempo');
        $tempoBinPhar = preg_replace('{^#!/usr/bin/env php\s*}', '', $tempoBinContents);
        $phar->addFromString('bin/tempo', $tempoBinPhar);

        // Stubs
        $stub = file_get_contents(__DIR__.'/tempo.phar.stub');
        $phar->setStub($stub);

        $phar->stopBuffering();
    }

    private function checkWorkingDirectory()
    {
        // Check we are in the right place
        if (getcwd() !== $this->baseDir) {
            throw new RuntimeException(sprintf('Please run this from %s', $this->baseDir));
        }

        // Check we are in a clean git working directory
        try {
            $gitStatus = $this->local->run('git status --porcelain');
        } catch (ProcessFailedException $e) {
            throw new RuntimeException('You must compile from a clean tagged git working copy', 0, $e);
        }

        if (!empty($gitStatus)) {
            throw new RuntimeException('Local copy unclean (see `git status`)');
        }
    }

    private function checkVersion()
    {
        $namerev = trim($this->local->run('git name-rev --tags --name-only HEAD'));
        if ($namerev === 'undefined') {
            throw new RuntimeException('You must be on a tagged version to compile a phar');
        }
        $version = preg_replace('/\^.*$/', '', $namerev);

        $tempoBinContents = file_get_contents($this->baseDir.'/bin/tempo');
        $matches = array();
        if (!preg_match('/^\$applicationVersion = \'(.*)\';$/m', $tempoBinContents, $matches)) {
            throw new RuntimeException('bin/tempo must contain "$applicationVersion = \'\';"');
        }

        if ($matches[1] !== $version) {
            throw new RuntimeException(sprintf(
                'bin/tempo - $applicationVersion = \'%s\'; does not match git tag (%s),'
                .' please update it before compiling',
                $matches[1],
                $version
            ));
        }
    }
}
