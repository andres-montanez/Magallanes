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

use Mage\Task\ErrorException;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Rsync Task - Copy files with Rsync
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class RsyncTask extends AbstractTask
{
    public function getName()
    {
        return 'deploy/rsync';
    }

    public function getDescription()
    {
        return '[Deploy] Copying files with Rsync';
    }

    public function execute()
    {
        $flags = $this->runtime->getConfigOptions('rsync', '-avz');
        $sshConfig = $this->runtime->getSSHConfig();
        $user = $this->runtime->getEnvironmentConfig('user');
        $host = $this->runtime->getWorkingHost();
        $hostPath = rtrim($this->runtime->getEnvironmentConfig('host_path'), '/');
        $targetDir = rtrim($hostPath, '/');

        if ($this->runtime->getEnvironmentConfig('releases', false)) {
            throw new ErrorException('Can\'t be used with Releases, use "deploy/targz/copy"');
        }

        $excludes = $this->getExcludes();
        $cmdRsync = sprintf('rsync -e "ssh -p %d %s" %s %s ./ %s@%s:%s', $sshConfig['port'], $sshConfig['flags'], $flags, $excludes, $user, $host, $targetDir);

        /** @var Process $process */
        $process = $this->runtime->runLocalCommand($cmdRsync, 600);
        return $process->isSuccessful();
    }

    protected function getExcludes()
    {
        $excludes = $this->runtime->getEnvironmentConfig('exclude', []);
        $excludes = array_merge(['.git'], $excludes);

        foreach ($excludes as &$exclude) {
            $exclude = '--exclude=' . $exclude;
        }

        return implode(' ', $excludes);
    }
}
