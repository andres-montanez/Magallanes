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

use Symfony\Component\Process\Process;

/**
 * Custom PreRegistered Task for Testing
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class InvalidInheritanceTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'custom-invalid-inheritance';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return '[Custom] Invalid Inheritance*';
    }

    /**
     * @return bool
     */
    public function execute()
    {
        /** @var Process $process */
        $process = $this->runtime->runCommand('echo "custom-invalid-inheritance"');
        return $process->isSuccessful();
    }
}
