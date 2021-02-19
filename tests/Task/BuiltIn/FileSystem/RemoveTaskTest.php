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
use Mage\Task\BuiltIn\FS\RemoveTask;
use Exception;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit\Framework\TestCase;

class RemoveTaskTest extends TestCase
{
    public function testRemoveTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new RemoveTask();
        $task->setOptions(['file' => 'a.txt']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('a.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'rm  "a.txt"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testRemoveWithFlagsTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new RemoveTask();
        $task->setOptions(['file' => 'a.txt', 'flags' => '-fr']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('a.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'rm -fr "a.txt"',
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

        $this->assertStringContainsString('test.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'rm  "test.txt"',
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
            $this->assertStringContainsString('[missing parameters]', $task->getDescription());
            $task->execute();
            $this->assertTrue(false, 'Task did not failed');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof ErrorException);
            $this->assertEquals('Parameter "file" is not defined', $exception->getMessage());
        }
    }
}
