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

use Mage\Runtime\Exception\RuntimeException;
use Mage\Task\BuiltIn\FS\CopyTask;
use Mage\Task\BuiltIn\FS\MoveTask;
use Mage\Task\BuiltIn\FS\RemoveTask;
use Exception;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit_Framework_TestCase as TestCase;

class FileSystemTaskTest extends TestCase
{
    public function testCopyTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new CopyTask();
        $task->setOptions(['from' => 'a.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cp -p a.txt b.txt',
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

        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cp -p test.txt b.txt',
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
            $task->execute();
            $this->assertTrue(false, 'Task did not failed');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('Parameter "from" is not defined', $exception->getMessage());
        }
    }

    public function testMoveTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new MoveTask();
        $task->setOptions(['from' => 'a.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'mv a.txt b.txt',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testMoveReplaceTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new MoveTask();
        $task->setOptions(['from' => '%environment%.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'mv test.txt b.txt',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testMoveBadOptionsTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new MoveTask();
        $task->setOptions(['form' => 'a.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        try {
            $task->execute();
            $this->assertTrue(false, 'Task did not failed');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('Parameter "from" is not defined', $exception->getMessage());
        }
    }

    public function testRemoveTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new RemoveTask();
        $task->setOptions(['file' => 'a.txt']);
        $task->setRuntime($runtime);

        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'rm a.txt',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testRemoveReplaceTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new RemoveTask();
        $task->setOptions(['file' => '%environment%.txt']);
        $task->setRuntime($runtime);

        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'rm test.txt',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testRemoveBadOptionsTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new RemoveTask();
        $task->setOptions(['from' => 'a.txt']);
        $task->setRuntime($runtime);

        try {
            $task->execute();
            $this->assertTrue(false, 'Task did not failed');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('Parameter "file" is not defined', $exception->getMessage());
        }
    }
}
