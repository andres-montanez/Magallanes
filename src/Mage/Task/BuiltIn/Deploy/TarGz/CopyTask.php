<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Deploy\TarGz;

use Mage\Runtime\Exception\DeploymentException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * TarGz Task - Copy Tar
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CopyTask extends AbstractTask
{
    public function getName()
    {
        return 'deploy/targz/copy';
    }

    public function getDescription()
    {
        return '[Deploy] Copying files with TarGZ';
    }

    public function execute()
    {
        if (!$this->runtime->getEnvironmentConfig('releases', false)) {
            throw new DeploymentException('This task is only available with releases enabled', 400);
        }

        $user = $this->runtime->getEnvironmentConfig('user');
        $host = $this->runtime->getWorkingHost();
        $scpFlags = $this->runtime->getEnvironmentConfig('scp', '-P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no');
        $hostPath = rtrim($this->runtime->getEnvironmentConfig('host_path'), '/');
        $currentReleaseId = $this->runtime->getReleaseId();

        $targetDir = sprintf('%s/releases/%s', $hostPath, $currentReleaseId);

        $tarGzLocal = $this->runtime->getVar('targz_local');
        $tarGzRemote = basename($tarGzLocal);

        $cmdCopy = sprintf('scp %s %s %s@%s:%s/%s', $scpFlags, $tarGzLocal, $user, $host, $targetDir, $tarGzRemote);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdCopy, 300);
        if ($process->isSuccessful()) {
            $cmdUntar = sprintf('cd %s && tar xfz %s', $targetDir, $tarGzRemote);
            $process = $this->runtime->runRemoteCommand($cmdUntar, false, 600);
            if ($process->isSuccessful()) {
                $cmdDelete = sprintf('rm %s/%s', $targetDir, $tarGzRemote);
                $process = $this->runtime->runRemoteCommand($cmdDelete, false);
                if ($process->isSuccessful()) {
                    return true;
                }
            }
        }

        return false;
    }
}
