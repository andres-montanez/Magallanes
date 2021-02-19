<?php

namespace Mage\tests\Task\BuiltIn\Symfony;


use Mage\Task\BuiltIn\Symfony\CachePoolClearTask;
use PHPUnit\Framework\TestCase;
use Mage\Tests\Runtime\RuntimeMockup;
use Mage\Task\Exception\ErrorException;

class CachePoolClearTaskTest extends TestCase
{
    /**
     * @var RuntimeMockup
     */
    private $runtime;

    public function setUp(): void
    {
        $this->runtime = new RuntimeMockup();
        $this->runtime->setConfiguration(['environments' => ['test' => []]]);
        $this->runtime->setEnvironment('test');
    }

    public function testAsseticDumpTask()
    {
        $task = new CachePoolClearTask();
        $task->setOptions(['env' => 'test']);
        $task->setRuntime($this->runtime);
        $this->assertEquals('[Symfony] Cache Pool Clear', $task->getDescription());

        try {
            $task->execute();
        } catch (ErrorException $exception) {
            $this->assertEquals('Parameter "pools" is not defined', $exception->getMessage());
        }
    }
}