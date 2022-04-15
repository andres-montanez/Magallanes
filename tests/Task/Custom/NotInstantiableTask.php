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
abstract class NotInstantiableTask extends AbstractTask
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'custom-not-instantiable';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return '[Custom] Not Instantiable*';
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        /** @var Process $process */
        $process = $this->runtime->runCommand('echo "custom-not-instantiable"');
        return $process->isSuccessful();
    }
}
