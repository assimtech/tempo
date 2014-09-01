<?php

namespace Tempo\Task\Deployment\Strategy;

use Tempo\Task;
use Tempo\Node\AbstractNode;

class LinkedReleases extends Task
{
    /**
     * Cleanup old releases for a given node and releases path
     * @param \Tempo\Node\AbstractNode $node
     * @param string $releasesPath Path to the releases (The directory containing all the release directories)
     * @param integer $keep The number of releases to keep
     */
    public function cleanupOld(AbstractNode $node, $releasesPath, $keep = 5)
    {
        // Get the releases
        $dirs = $node->run(sprintf(
            'ls %s',
            escapeshellarg($releasesPath)
        ));

        // As an array
        $releases = explode(PHP_EOL, $dirs);
        array_pop($releases); // The last one is a blank line

        // Delete the first few until we reach the $keep limit
        for ($i = 0; $i < (count($releases) - $keep); $i++) {
            $oldRelease = $releasesPath.'/'.$releases[$i];

            $this->output->writeln('Removing old release: ' . $oldRelease);

            $node->run(sprintf(
                'rm -r %s',
                escapeshellarg($oldRelease)
            ));
        }
    }
}
