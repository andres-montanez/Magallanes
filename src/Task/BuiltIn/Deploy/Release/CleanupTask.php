<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Deploy\Release;

use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Release Task - Cleanup old releases
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CleanupTask extends AbstractTask
{
    public function getName(): string
    {
        return 'deploy/release/cleanup';
    }

    public function getDescription(): string
    {
        return '[Release] Cleaning up old Releases';
    }

    public function execute(): bool
    {
        $hostPath = rtrim($this->runtime->getEnvOption('host_path'), '/');
        $currentReleaseId = $this->runtime->getReleaseId();
        $maxReleases = $this->runtime->getEnvOption('releases');

        $cmdListReleases = sprintf('ls -1 %s/releases', $hostPath);

        /** @var Process $process */
        $process = $this->runtime->runRemoteCommand($cmdListReleases, false);
        if ($process->isSuccessful()) {
            $releases = $process->getOutput();
            $releases = explode("\n", trim($releases));

            if (count($releases) > $maxReleases) {
                sort($releases);
                $releasesToDelete = array_slice($releases, 0, count($releases) - $maxReleases);
                foreach ($releasesToDelete as $releaseId) {
                    if ($releaseId !== $currentReleaseId) {
                        $cmdDeleteRelease = sprintf('rm -rf %s/releases/%s', $hostPath, $releaseId);
                        /** @var Process $process */
                        $process = $this->runtime->runRemoteCommand($cmdDeleteRelease, false);
                        if (!$process->isSuccessful()) {
                            return false;
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }
}
