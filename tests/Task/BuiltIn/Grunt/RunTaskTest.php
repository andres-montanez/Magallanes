<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task\BuiltIn\Grunt;

use Mage\Task\BuiltIn\Grunt\RunTask;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit_Framework_TestCase as TestCase;

class RunTaskTest extends TestCase
{
    public function testRunTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new RunTask();
        $task->setOptions(['flags' => '--flags']);
        $task->setRuntime($runtime);
        $this->assertEquals('[Grunt] Run', $task->getDescription());

        $task->execute();

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => 'grunt --flags',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }
}
