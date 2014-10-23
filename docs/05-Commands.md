# Commands

A tempo command is simply a `Symfony\Component\Console\Command\Command`; a single unit of work executed from the command
line.

For documentation on how to define a command please see,
<http://symfony.com/doc/current/components/console/introduction.html>


We typically use a command to do something like deploy software to a single environment.


## Example

In this scenario we have a command to deploy software to a single environment at a time. Before running it we would `cd`
into a git working directory which is checked out to the tag we wish to deploy. On the remote side, we have a symlink
pointing at the current release. The software will be built in a new release directory. Once the release is ready to go
live, we remove the current symlink and create a new one pointing at our new release directory. This is how
[Capistrano](http://capistranorb.com/) does deployments be default.

```php
    <?php

    use Assimtech\Tempo;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class Deploy extends Command
    {
        /**
         * @var \Assimtech\Tempo\Environment $env
         */
        private $env;

        /**
         * {@inheritdoc}
         */
        public function __construct(Tempo\Environment $env)
        {
            $this->env = $env;

            parent::__construct(sprintf('%s:deploy', $env));
        }

        /**
         * {@inheritdoc}
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $currentPath = '/var/www/example.com/current';
            $releasesPath = '/var/www/example.com/releases';
            $releasePath = $releasesPath.'/'.date('Y-m-d\TH:i:s');

            $local = new Tempo\Node\Local();
            $remote = $this->env->getNode();

            // Copy
            $output->writeln(sprintf('Copying to %s', $remote));
            $local->run(sprintf(
                'rsync -ltrz ./ %s',
                escapeshellarg($remote.':'.$releasePath)
            ));

            // Put it live
            $remote->run(sprintf(
                'rm -f %1$s && ln -s %2$s %1$s',
                escapeshellarg($currentPath),
                escapeshellarg($releasePath)
            ));
            $output->writeln(sprintf('We are live on %s', $remote));
        }
    }
```


## Rolling back

It's likely you will want to be able to roll a command back if it fails half way through. This is achieved by placing
`try () {} catch {}` blocks around sections of you command that could fail. It's normal to have more than one set of
`try () {} catch {}` blocks is a single command if you have multiple checkpoints to rollback from.

Say your command copies something to a new release directory then moves the current live symlink to it and does a cache
warm. There are two distinct rollback checkpoints; one beginning after the copy has started and one after symlink has
been switched. To roll this back you could:

```php
    try {
        // copy to new release directory
    } catch (Exception $e) {
        // Just delete the new release directory, no harm done.
        // Abort
    }

    try {
        // Move the current live symlink to the new release directory
    } catch (Exception $e) {
        // Move the current live symlink back to the old release directory
        // Delete the new release directory
        // Hope nobody saw the broken site while that symlink was pointed at the new release... email customer service
        // just incase
        // Abort
    }
```


### Rollback example

Suppose we want to make the above `Deploy` example more robust.

```php
    <?php

    use Assimtech\Tempo;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Exception;

    class Deploy extends Command
    {
        /**
         * @var \Assimtech\Tempo\Environment $env
         */
        private $env;

        /**
         * {@inheritdoc}
         */
        public function __construct(Tempo\Environment $env)
        {
            $this->env = $env;

            parent::__construct(sprintf('%s:deploy', $env));
        }

        /**
         * {@inheritdoc}
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $currentPath = '/var/www/example.com/current';
            $releasesPath = '/var/www/example.com/releases';
            $releasePath = $releasesPath.'/'.date('Y-m-d\TH:i:s');

            $local = new Tempo\Node\Local();
            $remote = $this->env->getNode();

            // Get the current release
            $dirs = $remote->run(sprintf(
                'ls %s',
                escapeshellarg($releasesPath)
            ));
            $releases = explode("\n", $dirs);
            array_pop($releases); // The last one is a blank line
            $currentRelease = array_pop($releases);

            // Copy
            try {
                $output->writeln(sprintf('Copying to %s', $remote));
                $local->run(sprintf(
                    'rsync -ltrz ./ %s',
                    escapeshellarg($remote.':'.$releasePath)
                ));
            } catch (Exception $e) {
                $output->writeln('Copy failed, rolling back, no harm done');
                $remote->run(sprintf(
                    'rm -r %s',
                    escapeshellarg($releasePath)
                ));
                throw $e;
            }

            try {
                // Put it live
                $remote->run(sprintf(
                    'rm -f %1$s && ln -s %2$s %1$s',
                    escapeshellarg($currentPath),
                    escapeshellarg($releasePath)
                ));
                $output->writeln(sprintf('We are live on %s', $remote));

                // Warm the cache
                $remote->run(sprintf(
                    '%s/app/console --env=%s cache:warm',
                    escapeshellarg($releasePath),
                    escapeshellarg($this->env)
                ));
            } catch (Exception $e) {
                $output->writeln('Cache warm failed, rolling back');
                $remote->run(sprintf(
                    'rm -f %1$s && ln -s %2$s %1$s',
                    escapeshellarg($currentPath),
                    escapeshellarg($releasesPath.'/'.$currentRelease)
                ));
                throw $e;
            }
        }
    }
```
