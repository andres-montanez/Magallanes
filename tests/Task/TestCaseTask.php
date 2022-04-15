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

class TestCaseTask extends AbstractTask
{
    public function getName(): string
    {
        return 'test';
    }

    public function getDescription(): string
    {
        return '[Test] This is a Test Task';
    }

    public function execute(): bool
    {
        return true;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
