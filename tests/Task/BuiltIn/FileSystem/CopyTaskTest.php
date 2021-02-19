<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task\BuiltIn\FileSystem;

use Mage\Task\Exception\ErrorException;
use Mage\Task\BuiltIn\FS\CopyTask;
use Exception;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit\Framework\TestCase;

class CopyTaskTest extends TestCase
{
    public function testCopyTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new CopyTask();
        $task->setOptions(['from' => 'a.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('a.txt', $task->getDescription());
        $this->assertStringContainsString('b.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cp -p "a.txt" "b.txt"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testCopyTaskWithFlags()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new CopyTask();
        $task->setOptions(['from' => 'a.txt', 'to' => 'b.txt', 'flags' => '-rp']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('a.txt', $task->getDescription());
        $this->assertStringContainsString('b.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cp -rp "a.txt" "b.txt"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testCopyReplaceTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new CopyTask();
        $task->setOptions(['from' => '%environment%.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('test.txt', $task->getDescription());
        $this->assertStringContainsString('b.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cp -p "test.txt" "b.txt"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testCopyMultipleReplaceTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');
        $runtime->setReleaseId('1234');
        $runtime->setWorkingHost('localhost');

        $task = new CopyTask();
        $task->setOptions(['from' => '%host%.txt', 'to' => '%release%.yml']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('localhost.txt', $task->getDescription());
        $this->assertStringContainsString('1234.yml', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cp -p "localhost.txt" "1234.yml"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testCopyBadOptionsTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new CopyTask();
        $task->setOptions(['form' => 'a.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        try {
            $this->assertStringContainsString('[missing parameters]', $task->getDescription());
            $task->execute();
            $this->assertTrue(false, 'Task did not failed');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof ErrorException);
            $this->assertEquals('Parameter "from" is not defined', $exception->getMessage());
        }
    }
}
