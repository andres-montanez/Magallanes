<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Abstract Base task to concentrate common code for Deployment Tasks
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
abstract class BaseStrategyTaskAbstract extends AbstractTask implements IsReleaseAware
{
    /**
     * Checks if there is an override underway
     *
     * @return bool
     */
    protected function checkOverrideRelease()
    {
        $overrideRelease = $this->getParameter('overrideRelease', false);

        if ($overrideRelease == true) {
            $releaseToOverride = false;
            $resultFetch = $this->runCommandRemote('ls -ld current | cut -d"/" -f2', $releaseToOverride);
            if ($resultFetch && is_numeric($releaseToOverride)) {
                $this->getConfig()->setReleaseId($releaseToOverride);
            }
        }

        return $overrideRelease;
    }

    /**
     * Gathers the files to exclude
     *
     * @return array
     */
    protected function getExcludes()
    {
        $excludes = array(
            '.git',
            '.svn',
            '.mage',
            '.gitignore',
            '.gitkeep',
            'nohup.out'
        );

        // Look for User Excludes
        $userExcludes = $this->getConfig()->deployment('excludes', array());

        return array_merge($excludes, $userExcludes);
    }

    /**
     * Removes old releases
     */
    protected function cleanUpReleases()
    {
        // Count Releases
        if ($this->getConfig()->release('enabled', false) == true) {
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
                                $result = $result && $this->runCommandRemote($command);
                            }
                        }
                    }
                }
            }
        }
    }
}
