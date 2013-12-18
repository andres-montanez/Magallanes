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
 * Task for Sync the Local Code to the Remote Hosts via Tar GZ
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class TarGzTask extends ReleasesAbstractTask implements IsReleaseAware
{
	/**
	 * (non-PHPdoc)
	 * @see \Mage\Task\AbstractTask::getName()
	 */
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) == true) {
                return 'Deploy via TarGz (with Releases override) [built-in]';
            } else {
                return 'Deploy via TarGz (with Releases) [built-in]';
            }
        } else {
                return 'Deploy via TarGz [built-in]';
        }
    }

    public function deploy()
    {
        list($localTarGz, $remoteTarGz) = $this->createTarGz();
        $this->copyTarToRemote($localTarGz);
        $this->extractTarGz($remoteTarGz);
        $this->deleteRemoteTarGz($remoteTarGz);
        $this->deleteLocalTarGz($localTarGz);
    }

    /**
     * @return array
     */
    protected function createTarGz()
    {
        $localTarGz = tempnam(sys_get_temp_dir(), 'mage');
        $remoteTarGz = basename($localTarGz);
        $excludes = array_merge($excludes, $userExcludes);
        $excludeCmd = '';
        foreach ($excludes as $excludeFile) {
            $excludeCmd .= ' --exclude=' . $excludeFile;
        }

        $command = 'tar cfz ' . $localTarGz . '.tar.gz ' . $excludeCmd . ' ' . $this->getConfig()->deployment('from');
        $result = $this->runCommandLocal($command);
    }
    /**
     * @param $localTarGz
     * @return string
     */
    protected function copyTarToRemote($localTarGz)
    {
        $command = 'scp -P ' . $this->getConfig()->getHostPort() . ' ' . $localTarGz . '.tar.gz '
            . $this->getConfig()->getNameAtHostnameString() . ':' . $this->getConfig()->getDeployToDirectory();
        $this->runJobLocal($command);
    }

    /**
     * @param $remoteTarGz
     * @return string
     */
    protected function extractTarGz($remoteTarGz)
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            $command = 'cd ' . $this->getConfig()->getDeployToDirectory() . ' && tar xfz ' . $remoteTarGz . '.tar.gz';
        } else {
            $command = 'tar xfz ' . $remoteTarGz . '.tar.gz';
        }
        $this->runJobRemote($command);
    }

    /**
     * @param $remoteTarGz
     * @return string
     */
    protected function deleteRemoteTarGz($remoteTarGz)
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            $command = 'rm ' . $this->getConfig()->getDeployToDirectory() . '/' . $remoteTarGz . '.tar.gz';
        } else {
            $command = 'rm ' . $remoteTarGz . '.tar.gz';
        }
        $this->runJobRemote($command);
    }

    /**
     * @param $localTarGz
     */
    protected function deleteLocalTarGz($localTarGz)
    {
        $command = 'rm ' . $localTarGz . ' ' . $localTarGz . '.tar.gz';
        $this->runJobLocal($command);
    }


}
