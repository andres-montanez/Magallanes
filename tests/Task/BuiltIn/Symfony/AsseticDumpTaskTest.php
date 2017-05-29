<?php

namespace Mage\tests\Task\BuiltIn\Symfony;


use Mage\Task\BuiltIn\Symfony\AsseticDumpTask;
use Mage\Tests\Runtime\RuntimeMockup;

class AsseticDumpTaskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RuntimeMockup
     */
    private $runtime;

    public function setUp()
    {
        $this->runtime = new RuntimeMockup();
        $this->runtime->setConfiguration(['environments' => ['test' => []]]);
        $this->runtime->setEnvironment('test');
    }

    public function testAsseticDumpTask()
    {
        $task = new AsseticDumpTask();
        $task->setOptions(['env' => 'test']);
        $task->setRuntime($this->runtime);
        $this->assertEquals('[Symfony] Assetic Dump', $task->getDescription());
        $task->execute();

        $testCase = [
            'bin/console assetic:dump --env=test' => 120,
        ];

        $this->assertRanCommands($testCase);
    }

    public function testAsseticDumpTaskWithTimeoutOption()
    {
        $task = new AsseticDumpTask();
        $task->setOptions(['env' => 'test', 'timeout' => 300]);
        $task->setRuntime($this->runtime);
        $task->execute();


        $testCase = [
            'bin/console assetic:dump --env=test' => 300,
        ];

        $this->assertRanCommands($testCase);
    }

    /**
     * @param $testCase
     */
    private function assertRanCommands($testCase)
    {
        $ranCommands = $this->runtime->getRanCommands();

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        $index = 0;
        foreach ($testCase as $command => $timeout) {
            $this->assertEquals($command, $ranCommands[$index++]);
            $this->assertEquals($timeout, $this->runtime->getRanCommandTimeoutFor($command));
        }
    }
}
