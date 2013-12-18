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
use Mage\Task\Releases\SkipOnOverride;

use Exception;

/**
 * Task for Releasing a Deploy
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ReleaseTask extends AbstractTask implements IsReleaseAware, SkipOnOverride
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        return 'Releasing [built-in]';
    }

    /**
     * Releases a Deployment: points the current symbolic link to the release directory
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            if (substr($symlink, 0, 1) == '/') {
                $releasesDirectory = rtrim($this->getConfig()->deployment('to'), '/') . '/' . $releasesDirectory;
            }

            $currentCopy = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();

            if (! $releaseUser = $this->getConfig()->getEnvironmentOption('release_user', null)) {
                // Fetch the user and group from base directory; defaults usergroup to 33:33
                $releaseUser = implode('', $this->runJobRemote('ls -ld . | awk \'{print \$3":"\$4}\'')->stdout);
                $releaseUser = $releaseUser ? $releaseUser : "33:33";
            }

            // Remove symlink if exists; create new symlink and change owners
            $command = "rm -f $symlink;"
                     . "ln -sf $currentCopy $symlink && "
                     . "chown -h $releaseUser $symlink && "
                     . "chown -R $releaseUser $currentCopy";
            $this->runJobRemote($command);

            // Set Directory Releases to same owner
            $this->runJobRemote("chown $releaseUser $releasesDirectory");

            return $this->isAllOk();

        } else {
            return false;
        }
    }

}
