<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task\Custom;

use Mage\Task\AbstractTask;
use Symfony\Component\Process\Process;

/**
 * Custom PreRegistered Task for Testing
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class ValidTask extends AbstractTask
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'custom-valid';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '[Custom] Valid*';
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        /** @var Process $process */
        $process = $this->runtime->runCommand('echo "custom-valid"');
        return $process->isSuccessful();
    }
}
