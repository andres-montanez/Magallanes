<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Deploy\Tar;

use Mage\Task\Exception\ErrorException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Tar Task - Copy Tar
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CopyTask extends AbstractTask
{
    public function getName()
    {
        return 'deploy/tar/copy';
    }

    public function getDescription()
    {
        return '[Deploy] Copying files with Tar';
    }

    public function execute()
    {
        if (!$this->runtime->getEnvOption('releases', false)) {
            throw new ErrorException('This task is only available with releases enabled', 40);
        }

        $user = $this->runtime->getEnvOption('user', $this->runtime->getCurrentUser());
        $host = $this->runtime->getWorkingHost();
        $sshConfig = $sshConfig = $this->runtime->getSSHConfig();
        $hostPath = rtrim($this->runtime->getEnvOption('host_path'), '/');
        $currentReleaseId = $this->runtime->getReleaseId();

        $tarPath = $this->runtime->getEnvOption('tar_extract_path', 'tar');
        $flags = $this->runtime->getEnvOption('tar_extract', 'xfzop');
        $timeout = $this->runtime->getEnvOption('tar_timeout', 600);
        $targetDir = sprintf('%s/releases/%s', $hostPath, $currentReleaseId);

        $tarLocal = $this->runtime->getVar('tar_local');
        $tarRemote = basename($tarLocal);

        $cmdCopy = sprintf('scp -P %d %s %s %s@%s:%s/%s', $sshConfig['port'], $sshConfig['flags'], $tarLocal, $user, $host, $targetDir, $tarRemote);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdCopy, 300);
        if ($process->isSuccessful()) {
            $cmdUnTar = sprintf('cd %s && %s %s %s', $targetDir, $tarPath, $flags, $tarRemote);
            $process = $this->runtime->runRemoteCommand($cmdUnTar, false, $timeout);
            if ($process->isSuccessful()) {
                $cmdDelete = sprintf('rm %s/%s', $targetDir, $tarRemote);
                $process = $this->runtime->runRemoteCommand($cmdDelete, false);
                return $process->isSuccessful();
            }
        }

        return false;
    }
}
