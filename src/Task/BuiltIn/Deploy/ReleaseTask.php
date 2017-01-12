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
use Mage\Task\ExecuteOnRollbackInterface;
use Symfony\Component\Process\Process;
use Mage\Task\AbstractTask;

/**
 * Release Task - Create the Symlink
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class ReleaseTask extends AbstractTask implements ExecuteOnRollbackInterface
{
    public function getName()
    {
        return 'deploy/release';
    }

    public function getDescription()
    {
        return '[Release] Creating Symlink';
    }

    public function execute()
    {
        if (!$this->runtime->getEnvOption('releases', false)) {
            throw new ErrorException('This task is only available with releases enabled', 40);
        }

        $hostPath = rtrim($this->runtime->getEnvOption('host_path'), '/');
        $releaseId = $this->runtime->getReleaseId();

        $cmdLinkRelease = sprintf('cd %s && ln -snf releases/%s current', $hostPath, $releaseId);

        /** @var Process $process */
        $process = $this->runtime->runRemoteCommand($cmdLinkRelease, false);
        return $process->isSuccessful();
    }
}
