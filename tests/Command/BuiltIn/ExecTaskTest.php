<?php

namespace Mage\Tests\Command\BuiltIn;

use Mage\Runtime\Runtime;
use Mage\Task\BuiltIn\ExecTask;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Process\Process;

class ExecTaskTest extends TestCase
{
    public function testBasics()
    {
        $task = new ExecTask();
        $this->assertSame('exec', $task->getName());
        $this->assertSame('[Exec] Executing custom command', $task->getDescription());
    }

    public function testCustomDescription()
    {
        $task = new ExecTask();
        $task->setOptions(['descr' => '[My project] This is my wonderful task']);
        $this->assertSame('[My project] This is my wonderful task', $task->getDescription());
    }

    /**
     * @expectedException \Mage\Task\Exception\ErrorException
     */
    public function testNoCommandProvided()
    {
        $task = new ExecTask();
        $task->execute();
    }

    public function testNonJailedCommand()
    {
        $runtime = $this->getMockBuilder(Runtime::class)
            ->setMethods(['runRemoteCommand'])
            ->getMock();

        $runtime
            ->expects($this->once())
            ->method('runRemoteCommand')
            ->with('rm -rf /')
            ->willReturn($this->mockProcess(true));


        $task = $this->getTask($runtime);
        $task->setOptions(['cmd' => 'rm -rf /', 'jail' => false]);
        $this->assertTrue($task->execute());
    }

    public function testRegularCommand()
    {
        $runtime = $this->getMockBuilder(Runtime::class)
            ->setMethods(['runCommand'])
            ->getMock();

        $runtime
            ->expects($this->once())
            ->method('runCommand')
            ->with('rm -rf /', 10)
            ->willReturn($this->mockProcess(true));

        $task = $this->getTask($runtime);
        $task->setOptions(['cmd' => 'rm -rf /', 'timeout' => 10]);
        $task->execute();
    }

    private function getTask($runtime)
    {
        $task = new ExecTask();
        $task->setRuntime($runtime);

        return $task;
    }

    private function mockProcess($successful)
    {
        $process = $this->getMockBuilder(Process::class)
            ->disableOriginalConstructor()
            ->getMock();
        $process
            ->expects($this->any())
            ->method('isSuccessful')
            ->willReturn($successful);

        return $process;
    }
}
