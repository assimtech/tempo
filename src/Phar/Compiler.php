<?php

namespace Assimtech\Tempo\Phar;

use Phar;
use Assimtech\Tempo\Node\Local;
use Symfony\Component\Finder\Finder;
use RuntimeException;

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
     * @var \Phar $phar
     */
    private $phar;

    /**
     * @var \Assimtech\Tempo\Node\Local $local
     */
    private $local;

    /**
     * @param \Assimtech\Tempo\Node\Local $local
     */
    public function __construct(Phar $phar, Local $local)
    {
        $this->phar = $phar;
        $this->local = $local;
        $this->baseDir = realpath(__DIR__.'/../../');
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

        $this->phar->setSignatureAlgorithm(Phar::SHA1);

        $this->phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->exclude('Test')
            ->in('vendor/composer')
            ->in('vendor/assimtech/sysexits')
            ->in('vendor/symfony/console')
            ->in('vendor/symfony/polyfill-mbstring')
            ->in('vendor/symfony/process')
            ->in('vendor/symfony/yaml')
            ->in('src')
        ;
        foreach ($finder as $file) {
            $this->phar->addFile($file);
        }
        $this->phar->addFile('vendor/autoload.php');

        // Add bin/tempo but without shebang
        $tempoBinContents = file_get_contents($this->baseDir.'/bin/tempo');
        $tempoBinPhar = preg_replace('{^#!/usr/bin/env php\s*}', '', $tempoBinContents);
        $this->phar->addFromString('bin/tempo', $tempoBinPhar);

        // Stubs
        $stub = file_get_contents(__DIR__.'/tempo.phar.stub');
        $this->phar->setStub($stub);

        $this->phar->stopBuffering();
    }

    private function checkWorkingDirectory()
    {
        // Check we are in the right place
        if (getcwd() !== $this->baseDir) {
            throw new RuntimeException(sprintf('Please run this from %s', $this->baseDir));
        }

        // Check we are in a clean git working directory
        $gitStatus = $this->local->run('git status --porcelain');
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
        if (!preg_match('/Application\(\'tempo\', \'(.+)\'\);$/m', $tempoBinContents, $matches)) {
            throw new RuntimeException('bin/tempo must contain "Application(\'tempo\', \'<version>\');"');
        }

        if ($matches[1] !== $version) {
            throw new RuntimeException(sprintf(
                'bin/tempo - version \'%s\' does not match git tag \'%s\', please update it before compiling',
                $matches[1],
                $version
            ));
        }
    }
}
