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
use Mage\Task\BuiltIn\FS\LinkTask;
use Exception;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit\Framework\TestCase;

class LinkTaskTest extends TestCase
{
    public function testLinkTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new LinkTask();
        $task->setOptions(['from' => 'a.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('a.txt', $task->getDescription());
        $this->assertStringContainsString('b.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'ln -snf "a.txt" "b.txt"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testLinkTaskWithFlags()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new LinkTask();
        $task->setOptions(['from' => 'a.txt', 'to' => 'b.txt', 'flags' => '-P']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('a.txt', $task->getDescription());
        $this->assertStringContainsString('b.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'ln -P "a.txt" "b.txt"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testLinkReplaceTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new LinkTask();
        $task->setOptions(['from' => '%environment%.txt', 'to' => 'b.txt']);
        $task->setRuntime($runtime);

        $this->assertStringContainsString('test.txt', $task->getDescription());
        $this->assertStringContainsString('b.txt', $task->getDescription());
        $task->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'ln -snf "test.txt" "b.txt"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testLinkBadOptionsTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new LinkTask();
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
