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

use Mage\Task\Exception\ErrorException;
use Mage\Task\BuiltIn\ExecTask;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit\Framework\TestCase;

class ExecTest extends TestCase
{
    public function testSimpleCommand()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new ExecTask();
        $task->setOptions(['cmd' => 'ls -l', 'desc' => 'Command description']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('[Exec] Command description', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'ls -l',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testSimpleCommandWithInterpolation()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');
        $runtime->setReleaseId('1234');

        $task = new ExecTask();
        $task->setOptions(['cmd' => 'cp %environment%.env /app/%release%/.env', 'desc' => 'Copy config']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('[Exec] Copy config', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cp test.env /app/1234/.env',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testCommandWithoutDescription()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new ExecTask();
        $task->setOptions(['cmd' => 'ls -la']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('[Exec] Custom command', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'ls -la',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testWithoutCommand()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new ExecTask();
        $task->setOptions(['desc' => 'Loading docker']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('[Exec] Loading docker', $task->getDescription());

        try {
            $task->execute();
            $this->assertTrue(false, 'Task did not failed');
        } catch (\Exception $exception) {
            $this->assertTrue($exception instanceof ErrorException);
            $this->assertEquals('Parameter "cmd" is not defined', $exception->getMessage());
        }
    }
}
