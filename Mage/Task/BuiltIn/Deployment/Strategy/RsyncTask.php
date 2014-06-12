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

use Mage\Task\BuiltIn\Deployment\Strategy\BaseStrategyTaskAbstract;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Task for Sync the Local Code to the Remote Hosts via RSYNC
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RsyncTask extends BaseStrategyTaskAbstract implements IsReleaseAware
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) == true) {
                return 'Deploy via Rsync (with Releases override) [built-in]';
            } else {
                $rsync_copy = $this->getConfig()->deployment("rsync");
                if ( $rsync_copy && is_array($rsync_copy) && $rsync_copy['copy'] ) {
                    return 'Deploy via Rsync (with Releases) [built-in, incremental]';
                } else {
                    return 'Deploy via Rsync (with Releases) [built-in]';
                }
            }
        } else {
                return 'Deploy via Rsync [built-in]';
        }
    }

    /**
     * Syncs the Local Code to the Remote Host
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $this->checkOverrideRelease();

        $excludes = $this->getExcludes();

        // If we are working with releases
        $deployToDirectory = $this->getConfig()->deployment('to');
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');

            $currentRelease = false;
            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                               . '/' . $releasesDirectory
                               . '/' . $this->getConfig()->getReleaseId();
            $resultFetch = $this->runCommandRemote('ls -ld current | cut -d"/" -f2', $currentRelease);

            if ($resultFetch && $currentRelease) {
                // If deployment configuration is rsync, include a flag to simply sync the deltas between the prior release
                // rsync: { copy: yes }
                $rsync_copy = $this->getConfig()->deployment('rsync');
                if ( $rsync_copy && is_array($rsync_copy) && $rsync_copy['copy'] ) {
                    $this->runCommandRemote('cp -R ' . $releasesDirectory . '/' . $currentRelease . ' ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
                } else {
                    $this->runCommandRemote('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
                }
            }
        }

        $command = 'rsync -avz '
                 . '--rsh="ssh ' . $this->getConfig()->getHostIdentityFileOption() . '-p' . $this->getConfig()->getHostPort() . '" '
                 . $this->excludes($excludes) . ' '
                 . $this->getConfig()->deployment('from') . ' '
                 . $this->getConfig()->deployment('user') . '@' . $this->getConfig()->getHostName() . ':' . $deployToDirectory;

        $result = $this->runCommandLocal($command);

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

        return $result;
    }

    /**
     * Generates the Excludes for rsync
     * @param array $excludes
     * @return string
     */
    protected function excludes(Array $excludes)
    {
        $excludesRsync = '';
        foreach ($excludes as $exclude) {
            $excludesRsync .= ' --exclude ' . $exclude . ' ';
        }

        $excludesRsync = trim($excludesRsync);
        return $excludesRsync;
    }
}
