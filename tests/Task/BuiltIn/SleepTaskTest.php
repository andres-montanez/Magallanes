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
use PHPUnit\Framework\TestCase;

class SleepTaskTest extends TestCase
{
    public function testTaskWithDefault()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SleepTask();
        $task->setRuntime($runtime);

        $this->assertSame('[Sleep] Sleeping for 1 second(s)', $task->getDescription());
        $task->execute();
    }

    public function testTaskWithValue()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SleepTask();
        $task->setOptions(['seconds' => 2]);
        $task->setRuntime($runtime);

        $this->assertSame('[Sleep] Sleeping for 2 second(s)', $task->getDescription());

        $startedAt = microtime(true);
        $task->execute();
        $finishedAt = microtime(true);

        $this->assertGreaterThanOrEqual(2, $finishedAt - $startedAt);
    }
}
