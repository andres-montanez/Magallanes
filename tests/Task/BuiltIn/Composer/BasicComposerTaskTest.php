<?php

namespace Mage\Tests\Task\BuiltIn\Composer;

use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit\Framework\TestCase;

class BasicComposerTaskTest extends TestCase
{
    public function testBasicTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new BasicComposerTask();
        $task->setRuntime($runtime);
        $this->assertEquals('[Composer] Help', $task->getDescription());

        $task->execute();

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => 'composer help',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

}
