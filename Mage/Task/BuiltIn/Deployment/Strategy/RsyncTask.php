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

use Exception;

/**
 * Task for Sync the Local Code to the Remote Hosts via RSYNC
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RsyncTask extends ReleasesAbstractTask implements IsReleaseAware
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
                return 'Deploy via Rsync (with Releases) [built-in]';
            }
        } else {
                return 'Deploy via Rsync [built-in]';
        }
    }

    /**
     * Syncs the Local Code to the Remote Host
     * @see \Mage\Task\AbstractTask::run()
     */
    public function deploy()
    {
        $command = 'rsync -avz '
                 . '--rsh="ssh -p' . $this->getConfig()->getHostPort() . '" '
                 . $this->getConfig()->deployment('from') . ' '
                 . $this->getConfig()->getNameAtHostnameString() . ':' . $this->getConfig()->getDeployToDirectory()
                 . $this->getExcludesCommand($this->getConfig()->deployment('excludes', []),'--exclude ')
        ;

        $this->runJobLocal($command);
    }
}