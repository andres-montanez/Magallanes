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
 * Task for using Git Working Copy as the Deployed Code
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class GitRebaseTask extends BaseStrategyTaskAbstract implements IsReleaseAware
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Deploy via Git Rebase [built-in]';
    }

    /**
     * Rebases the Git Working Copy as the Deployed Code
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

            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                               . '/' . $releasesDirectory
                               . '/' . $this->getConfig()->getReleaseId();
            $this->runCommandRemote('mkdir -p ' . $releasesDirectory . '/' . $this->getConfig()->getReleaseId());
        }

        $branch = $this->getParameter('branch', 'master');
        $remote = $this->getParameter('remote', 'origin');

        // Fetch Remote
        $command = $this->getReleasesAwareCommand('git fetch ' . $remote);
        $result = $this->runCommandRemote($command);

        if ($result === false) {
            $repository = $this->getConfig()->deployment('repository');
            if ($repository) {
                $command = $this->getReleasesAwareCommand('git clone ' . $repository . ' .');
                $result = $this->runCommandRemote($command);

                $command = $this->getReleasesAwareCommand('git fetch ' . $remote);
                $result = $this->runCommandRemote($command);
            }
        }

        // Checkout
        $command = $this->getReleasesAwareCommand('git checkout ' . $branch);
        $result = $this->runCommandRemote($command) && $result;

        // Check Working Copy status
        $stashed = false;
        $status = '';
        $command = $this->getReleasesAwareCommand('git checkout ' . $branch);
        $result = $this->runCommandRemote($command) && $result;

        // Stash if Working Copy is not clean
        if(!$status) {
            $stashResult = '';
            $command = $this->getReleasesAwareCommand('git stash');
            $result = $this->runCommandRemote($command, $stashResult) && $result;
            if($stashResult != "No local changes to save") {
                $stashed = true;
            }
        }

        // Rebase
        $command = $this->getReleasesAwareCommand('git rebase ' . $remote . '/' . $branch);
        $result = $this->runCommandRemote($command) && $result;

        // If Stashed, restore.
        if ($stashed) {
            $command = $this->getReleasesAwareCommand('git stash pop');
            $result = $this->runCommandRemote($command) && $result;
        }

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
}
