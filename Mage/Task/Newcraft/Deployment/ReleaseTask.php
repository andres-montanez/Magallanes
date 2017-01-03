<?php
/*
* This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\Newcraft\Deployment;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\Releases\SkipOnOverride;

/**
 * Task for Releasing a Deploy, modified so no chown command is used.
 *
 */
class ReleaseTask extends AbstractTask implements IsReleaseAware, SkipOnOverride
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Releasing [newcraft]';
    }

    /**
     * Releases a Deployment: points the current symbolic link to the release directory
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');
            $chown = $this->getConfig()->release('chown', true);

            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }

            $releaseId = $this->getConfig()->getReleaseId();

            $currentCopy = $releasesDirectory . '/' . $releaseId;

            // Switch symlink and change owner. using `mv -T` is essential for some reason, don't just overwrite.
            $command = 'ln -sfn '.$currentCopy.' '.$symlink.'.tmp && mv -fT '.$symlink.'.tmp '.$symlink;
            $result = $this->runCommandRemote($command, $output);

            if ($result) {
                //if this is a php-fpm setup (like vagrant), reload the service to clear opcache path.
                if('vagrant' === $this->getConfig()->getEnvironment()) {
                    $this->runCommandRemote('sudo service php5-fpm reload');
                }
                $this->cleanUpReleases();
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Removes old releases
     */
    protected function cleanUpReleases()
    {
        // Count Releases
        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }

            $maxReleases = $this->getConfig()->release('max', false);
            if (($maxReleases !== false) && ($maxReleases > 0)) {
                $releasesList = '';
                $countReleasesFetch = $this->runCommandRemote('ls -1 ' . $releasesDirectory, $releasesList);
                $releasesList = trim($releasesList);

                if ($countReleasesFetch && $releasesList != '') {
                    $releasesList = explode(PHP_EOL, $releasesList);
                    if (count($releasesList) > $maxReleases) {
                        $releasesToDelete = array_diff($releasesList, array($this->getConfig()->getReleaseId()));
                        sort($releasesToDelete);
                        $releasesToDeleteCount = count($releasesToDelete) - $maxReleases;
                        $releasesToDelete = array_slice($releasesToDelete, 0, $releasesToDeleteCount + 1);

                        foreach ($releasesToDelete as $releaseIdToDelete) {
                            $directoryToDelete = $releasesDirectory . '/' . $releaseIdToDelete;
                            if ($directoryToDelete != '/') {
                                $command = 'rm -rf ' . $directoryToDelete;
                                $this->runCommandRemote($command);
                            }
                        }
                    }
                }
            }
        }
    }
}
