<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task\BuiltIn;

use Mage\Task\BuiltIn\SleepTask;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit_Framework_TestCase as TestCase;

class SleepTaskTest extends TestCase
{
    public function testCommand()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SleepTask();
        $task->setRuntime($runtime);

        $this->assertSame('[Sleep] Sleeping for 1 second(s)', $task->getDescription());
        $task->execute();
    }
}
