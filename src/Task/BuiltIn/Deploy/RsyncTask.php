<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Deploy;

use Mage\Task\Exception\ErrorException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Rsync Task - Copy files with Rsync
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class RsyncTask extends AbstractTask
{
    public function getName(): string
    {
        return 'deploy/rsync';
    }

    public function getDescription(): string
    {
        return '[Deploy] Copying files with Rsync';
    }

    public function execute(): bool
    {
        $flags = $this->runtime->getEnvOption('rsync', '-avz');
        $sshConfig = $this->runtime->getSSHConfig();
        $user = $this->runtime->getEnvOption('user', $this->runtime->getCurrentUser());
        $host = $this->runtime->getHostName();
        $hostPath = rtrim($this->runtime->getEnvOption('host_path'), '/');
        $targetDir = rtrim($hostPath, '/');

        if ($this->runtime->getEnvOption('releases', false)) {
            throw new ErrorException('Can\'t be used with Releases, use "deploy/tar/copy"');
        }

        $excludes = $this->getExcludes();
        $from = $this->runtime->getEnvOption('from', './');
        $cmdRsync = sprintf(
            'rsync -e "ssh -p %d %s" %s %s %s %s@%s:%s',
            $sshConfig['port'],
            $sshConfig['flags'],
            $flags,
            $excludes,
            $from,
            $user,
            $host,
            $targetDir
        );

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdRsync, 0);
        return $process->isSuccessful();
    }

    protected function getExcludes(): string
    {
        $excludes = $this->runtime->getMergedOption('exclude', []);
        $excludes = array_merge(['.git'], array_filter($excludes));

        foreach ($excludes as &$exclude) {
            $exclude = '--exclude=' . $exclude;
        }

        return implode(' ', $excludes);
    }
}
