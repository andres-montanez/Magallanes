<?php

namespace Mage\Tests\Command\BuiltIn\Composer;

use Mage\Runtime\Runtime;
use Mage\Task\BuiltIn\Composer\SelfUpdateTask;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\Process\Process;

class SelfUpdateTaskTest extends TestCase
{
    public function testBasics()
    {
        $task = new SelfUpdateTask();
        $this->assertSame('composer/selfupdate', $task->getName());
        $this->assertSame('[Composer] Selfupdate', $task->getDescription());
    }

    public function testExecuteWithFailingVersionDoesNotCallSelfupdate()
    {
        $runtime = $this->getMockBuilder(Runtime::class)
            ->setMethods(['runCommand'])
            ->getMock();

        $runtime
            ->expects($this->once())
            ->method('runCommand')
            ->with('composer --version')
            ->willReturn($this->mockProcess(false));

        $task = $this->getTask($runtime);
        $this->assertFalse($task->execute());
    }

    public function testExecuteWithNoDateVersionDoesCallSelfupdate()
    {
        $runtime = $this->getMockBuilder(Runtime::class)
                        ->setMethods(['runCommand'])
                        ->getMock();

        $runtime
            ->expects($this->exactly(2))
            ->method('runCommand')
            ->withConsecutive(
                ['composer --version'],
                ['composer selfupdate']
            )
            ->willReturnOnConsecutiveCalls(
                $this->mockProcess(true, 'whatever-without-valid-date'),
                $this->mockProcess(true)
            );

        $task = $this->getTask($runtime);
        $this->assertTrue($task->execute());
    }

    public function testExecuteShouldUpdate()
    {
        $runtime = $this->getMockBuilder(Runtime::class)
                        ->setMethods(['runCommand'])
                        ->getMock();

        $runtime
            ->expects($this->exactly(2))
            ->method('runCommand')
            ->withConsecutive(
                ['composer --version'],
                ['composer selfupdate']
            )
            ->willReturnOnConsecutiveCalls(
                $this->mockProcess(true, 'Composer version 1.3.2 2017-01-01 18:23:41'),
                $this->mockProcess(true)
            );

        $task = $this->getTask($runtime);
        $task->setOptions(['days' => 30]);
        $this->assertTrue($task->execute());
    }

    public function testExecuteShouldNotUpdate()
    {
        $runtime = $this->getMockBuilder(Runtime::class)
                        ->setMethods(['runCommand'])
                        ->getMock();

        $runtime
            ->expects($this->exactly(1))
            ->method('runCommand')
            ->with('composer --version')
            ->willReturn($this->mockProcess(true, 'Composer version 1.3.2 2017-01-01 18:23:41'));

        $task = $this->getTask($runtime);
        $task->setDateToCompare(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-12-10 18:23:41'));
        $task->setOptions(['days' => 30]);
        $this->assertTrue($task->execute());
    }

    public function testWithRelease()
    {
        $runtime = $this->getMockBuilder(Runtime::class)
            ->setMethods(['runCommand'])
            ->getMock();

        $runtime
            ->expects($this->exactly(2))
            ->method('runCommand')
            ->withConsecutive(
                ['composer --version'],
                ['composer selfupdate 1.3.1']
            )
            ->willReturnOnConsecutiveCalls(
                $this->mockProcess(true, 'Composer version 1.3.2 2017-01-01 18:23:41'),
                $this->mockProcess(true)
            );

        $task = $this->getTask($runtime);
        $task->setOptions(['days' => 30, 'release' => '1.3.1']);
        $this->assertTrue($task->execute());
    }

    private function getTask($runtime)
    {
        $config = [
            'magephp'   => [
                'composer' => [
                    'path' => 'composer.phar'
                ]
            ]
        ];

        /** @var Runtime $runtime */
        $runtime->setConfiguration($config);

        $task = new SelfUpdateTask();
        $task->setRuntime($runtime);

        return $task;
    }

    private function mockProcess($successful, $output = '')
    {
        $process = $this->getMockBuilder(Process::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $process
            ->expects($this->any())
            ->method('isSuccessful')
            ->willReturn($successful);

        $process
            ->expects($this->any())
            ->method('getOutput')
            ->willReturn($output);

        return $process;
    }
}
