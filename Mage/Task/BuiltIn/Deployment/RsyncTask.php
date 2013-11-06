<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;

use Exception;

/**
 * Task for Sync the Local Code to the Remote Hosts via RSYNC
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RsyncTask extends AbstractTask implements IsReleaseAware
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) == true) {
                return 'Rsync (with Releases override) [built-in]';
            } else {
                return 'Rsync (with Releases) [built-in]';
            }
        } else {
                return 'Rsync [built-in]';
        }
    }

    /**
     * Syncs the Local Code to the Remote Host
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $overrideRelease = $this->getParameter('overrideRelease', false);

        if ($overrideRelease == true) {
            $releaseToOverride = false;
            $resultFetch = $this->runCommandRemote('ls -ld current | cut -d"/" -f2', $releaseToOverride);
            if (is_numeric($releaseToOverride)) {
                $this->getConfig()->setReleaseId($releaseToOverride);
            }
        }

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

        // If we are working with releases
        $deployToDirectory = $this->getConfig()->deployment('to');
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');

            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                               . '/' . $releasesDirectory
                               . '/' . $this->getConfig()->getReleaseId();
            $this->runCommandRemote('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
        }

        $command = 'rsync -avz '
                 . '--rsh="ssh -p' . $this->getConfig()->getHostPort() . '" '
                 . $this->excludes(array_merge($excludes, $userExcludes)) . ' '
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

                if ($releasesList != '') {
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