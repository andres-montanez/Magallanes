<?php

namespace Mage\Tests\Task\BuiltIn\Composer;

use Mage\Tests\Runtime\RuntimeMockup;
use Mage\Task\BuiltIn\Composer\SelfUpdateTask;
use Mage\Task\Exception\SkipException;
use Exception;
use PHPUnit_Framework_TestCase as TestCase;

class SelfUpdateTaskTest extends TestCase
{
    public function testSelfUpdateTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SelfUpdateTask();
        $task->setOptions(['path' => 'composer']);
        $task->setRuntime($runtime);
        $this->assertEquals('[Composer] Self Update', $task->getDescription());

        try {
            $task->execute();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof SkipException, 'Update should been skipped');
        }

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => 'composer --version',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testSelfUpdateAsRootTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SelfUpdateTask();
        $task->setOptions(['path' => './composer']);
        $task->setRuntime($runtime);

        try {
            $task->execute();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof SkipException, 'Update should been skipped');
        }

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => './composer --version',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testSelfUpdateMustUpdateTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SelfUpdateTask();
        $task->setOptions(['path' => 'composer.phar']);
        $task->setRuntime($runtime);

        try {
            $result = $task->execute();
            $this->assertTrue($result, 'Result should be successful');
        } catch (Exception $exception) {
            if ($exception instanceof SkipException) {
                $this->assertTrue(false, 'Update should not have been skipped');
            }
        }

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => 'composer.phar --version',
            1 => 'composer.phar self-update',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testSelfUpdateWrongOutputTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SelfUpdateTask();
        $task->setOptions(['path' => 'php composer']);
        $task->setRuntime($runtime);

        try {
            $result = $task->execute();
            $this->assertFalse($result, 'Result should be failure');
        } catch (Exception $exception) {
            if ($exception instanceof SkipException) {
                $this->assertTrue(false, 'Update should not have been skipped');
            }
        }

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => 'php composer --version',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testSelfUpdateFailExecTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new SelfUpdateTask();
        $task->setOptions(['path' => 'composer']);
        $task->setRuntime($runtime);
        $runtime->forceFail('composer --version');

        try {
            $result = $task->execute();
            $this->assertFalse($result, 'Result should be failure');
        } catch (Exception $exception) {
            if ($exception instanceof SkipException) {
                $this->assertTrue(false, 'Update should not have been skipped');
            }
        }

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => 'composer --version',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }
}
