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
 * Task for Sync the Local Code to the Remote Hosts via Tar GZ
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class TarGzTask extends BaseStrategyTaskAbstract implements IsReleaseAware
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        if ($this->getConfig()->release('enabled', false) === true) {
            if ($this->getConfig()->getParameter('overrideRelease', false) === true) {
                return 'Deploy via TarGz (with Releases override) [built-in]';
            } else {
                return 'Deploy via TarGz (with Releases) [built-in]';
            }
        } else {
            return 'Deploy via TarGz [built-in]';
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
        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $deployToDirectory = rtrim($this->getConfig()->deployment('to'), '/')
                . '/' . $releasesDirectory
                . '/' . $this->getConfig()->getReleaseId();
            $output = null;
            $this->runCommandRemote('mkdir -p ' . $deployToDirectory, $output, false);
        }

        // Create Tar Gz
        $localTarGz = tempnam(sys_get_temp_dir(), 'mage');
        $remoteTarGz = basename($localTarGz);
        $excludeCmd = '';
        foreach ($excludes as $excludeFile) {
            $excludeCmd .= ' --exclude=' . $excludeFile;
        }

        // Strategy Flags
        $strategyFlags = $this->getConfig()->deployment('strategy_flags', $this->getConfig()->general('strategy_flags', array()));
        if (isset($strategyFlags['targz']) && isset($strategyFlags['targz']['create'])) {
            $strategyFlags = $strategyFlags['targz']['create'];
        } else {
            $strategyFlags = '';
        }

        $command = 'tar cfzh' . $strategyFlags . ' ' . $localTarGz . '.tar.gz ' . $excludeCmd . ' -C ' . $this->getConfig()->deployment('from') . ' .';
        $result = $this->runCommandLocal($command);

        // Strategy Flags
        $strategyFlags = $this->getConfig()->deployment('strategy_flags', $this->getConfig()->general('strategy_flags', array()));
        if (isset($strategyFlags['targz']) && isset($strategyFlags['targz']['exctract'])) {
            $strategyFlags = $strategyFlags['targz']['exctract'];
        } else {
            $strategyFlags = '';
        }

        // Copy Tar Gz  to Remote Host
        $command = 'scp ' . $strategyFlags . ' ' . $this->getConfig()->getHostIdentityFileOption() . '-P ' . $this->getConfig()->getHostPort() . ' ' . $localTarGz . '.tar.gz '
                 . $this->getConfig()->deployment('user') . '@' . $this->getConfig()->getHostName() . ':' . $deployToDirectory;
        $result = $this->runCommandLocal($command) && $result;

        // Strategy Flags
        $strategyFlags = $this->getConfig()->deployment('strategy_flags', $this->getConfig()->general('strategy_flags', array()));
        if (isset($strategyFlags['targz']) && isset($strategyFlags['targz']['scp'])) {
            $strategyFlags = $strategyFlags['targz']['scp'];
        } else {
            $strategyFlags = '';
        }

        // Extract Tar Gz
        $command = $this->getReleasesAwareCommand('tar xfz' . $strategyFlags . ' ' . $remoteTarGz . '.tar.gz');
        $result = $this->runCommandRemote($command) && $result;

        // Delete Tar Gz from Remote Host
        $command = $this->getReleasesAwareCommand('rm ' . $remoteTarGz . '.tar.gz');
        $result = $this->runCommandRemote($command) && $result;

        // Delete Tar Gz from Local
        $command = 'rm ' . $localTarGz . ' ' . $localTarGz . '.tar.gz';
        $result = $this->runCommandLocal($command) && $result;

        return $result;
    }
}
