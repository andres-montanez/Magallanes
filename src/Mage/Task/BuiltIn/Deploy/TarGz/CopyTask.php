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

use Mage\Task\ErrorException;
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
            throw new ErrorException('This task is only available with releases enabled', 40);
        }

        $user = $this->runtime->getEnvironmentConfig('user', $this->runtime->getCurrentUser());
        $host = $this->runtime->getWorkingHost();
        $sshConfig = $sshConfig = $this->runtime->getSSHConfig();
        $hostPath = rtrim($this->runtime->getEnvironmentConfig('host_path'), '/');
        $currentReleaseId = $this->runtime->getReleaseId();

        $targetDir = sprintf('%s/releases/%s', $hostPath, $currentReleaseId);

        $tarGzLocal = $this->runtime->getVar('targz_local');
        $tarGzRemote = basename($tarGzLocal);

        $cmdCopy = sprintf('scp -P %d %s %s %s@%s:%s/%s', $sshConfig['port'], $sshConfig['flags'], $tarGzLocal, $user, $host, $targetDir, $tarGzRemote);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdCopy, 300);
        if ($process->isSuccessful()) {
            $cmdUnTar = sprintf('cd %s && tar xfz %s', $targetDir, $tarGzRemote);
            $process = $this->runtime->runRemoteCommand($cmdUnTar, false, 600);
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
