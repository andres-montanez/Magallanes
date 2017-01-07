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

use Mage\Task\Exception\ErrorException;
use Mage\Task\AbstractTask;

class TestCaseFailTask extends AbstractTask
{
    public function getName()
    {
        return 'test-fail';
    }

    public function getDescription()
    {
        return '[Test] This is a Test Task which Fails';
    }

    public function execute()
    {
        throw new ErrorException('This is a text with a lot of characters');
    }
}
