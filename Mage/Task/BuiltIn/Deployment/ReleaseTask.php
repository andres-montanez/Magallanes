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

            //Check if target user:group is specified
            $userGroup = $this->getConfig()->deployment('owner');
            // Fetch the user and group from base directory; defaults usergroup to 33:33
            if(empty($userGroup)){
                $user = '33';
                $group = '33';
                $directoryInfos = '';
                // Get raw directory info and parse it in php.
                // "stat" command don't behave the same on different systems, ls output format also varies
                // and awk parameters need special care depending on the executing shell
                $resultFetch = $this->runCommandRemote("ls -ld .", $directoryInfos);
                if(!empty($directoryInfos)){
                    //uniformize format as it depends on the system deployed on
                    $directoryInfos = trim(str_replace(array("  ", "\t"), ' ', $directoryInfos));
                    $infoArray = explode(' ', $directoryInfos);
                    if(!empty($infoArray[2])) {
                        $user = $infoArray[2];
                    }
                    if(!empty($infoArray[3])) {
                        $group = $infoArray[3];
                    }
                    $userGroup = $user . ':' . $group;
                }
            }

            // Remove symlink if exists; create new symlink and change owners
            $command = 'rm -f ' . $symlink
                     . ' ; '
                     . 'ln -sf ' . $currentCopy . ' ' . $symlink;

            if ($resultFetch && $userGroup != '') {
            	$command .= ' && '
                          . 'chown -h ' . $userGroup . ' ' . $symlink
                          . ' && '
                          . 'chown -R ' . $userGroup . ' ' . $currentCopy
                          . ' && '
                          . 'chown ' . $userGroup . ' ' . $releasesDirectory;
            }

            $result = $this->runCommandRemote($command);

            return $result;

        } else {
            return false;
        }
    }

}
