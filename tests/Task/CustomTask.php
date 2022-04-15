<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task;

use Mage\Task\AbstractTask;

/**
 * Custom Task for Testing
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class CustomTask extends AbstractTask
{
    public function getName(): string
    {
        return 'custom';
    }

    public function getDescription(): string
    {
        return '[Custom] Dummy Task';
    }

    public function execute(): bool
    {
        return true;
    }
}
