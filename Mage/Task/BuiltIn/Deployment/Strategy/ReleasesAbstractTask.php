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

use Mage\Task\SkipException;

/**
 * Task for Sync the Local Code to the Remote Hosts via Tar GZ
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ReleasesAbstractTask extends AbstractTask implements IsReleaseAware
{

    protected $log = [];

    protected $defaultExcludes = [
            '.git',
            '.svn',
            '.mage',
            '.gitignore',
            '.gitkeep',
            'nohup.out'
    ];

    public function getName() {
        return '"No deploy" release task';
    }

    public function run() {
        $this->overrideRelease();
        $this->createDeployToDirectory();
        $this->deploy();
        $this->cleanReleases();

        return $this->isAllOk();
    }

    public function deploy() {
        throw new SkipException;
    }

    protected function createDeployToDirectory() {
        $deployToDirectory = $this->getConfig()->getDeployToDirectory();
        $this->runJobRemote("mkdir -p  $deployToDirectory");
    }

    protected function getExcludesParameters(array $userExcludes, $excludeKey)
    {
        $excludes = array_merge([''], $this->defaultExcludes, $userExcludes);
        return implode(" $excludeKey",$excludes);
    }

    protected function overrideRelease() {
        if ($this->getParameter('overrideRelease', false)) {
            $job = $this->runJobRemote('ls -ld current | cut -d"/" -f2');
            if (is_numeric($job->stdout)) {
                $this->getConfig()->setReleaseId($job->stdout);
            }
        }
    }

    protected function cleanReleases() {
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }

            $maxReleases = $this->getConfig()->release('max', false);
            if (($maxReleases !== false) && ($maxReleases > 0)) {
                $job = $this->runJobRemote("ls -1r $releasesDirectory  | tail -n +".($maxReleases+1));
                $releasesToDelete = implode(' ', $job->stdout);
                $command = "cd $releasesDirectory; rm -rf $releasesToDelete";
                $this->runJobRemote($command);
            }
        }
    }
}