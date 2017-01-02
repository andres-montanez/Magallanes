<?php

namespace MageTest\Task;

use Mage\Task\Factory;
use PHPUnit_Framework_TestCase;

/**
 * @group MageTest_Task_Factory
 * @group MageTest_Task
 * @group issue-176
 *
 * @uses Mage\Task\AbstractTask
 * @coversDefaultClass Mage\Task\Factory
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $config;

    protected function setUp()
    {
        $this->config = $this->getMock('Mage\\Config');
    }

    /**
     * @covers Mage\Task\Factory::get
     */
    public function testGet()
    {
        $task = Factory::get('composer/install', $this->config);
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Composer\\InstallTask', $task);
    }

    /**
     * @covers Mage\Task\Factory::get
     */
    public function testGetTaskDataIsArray()
    {
        $taskData = array(
            'name' => 'composer/install',
            'parameters' => array(),
        );

        $task = Factory::get($taskData, $this->config);
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Composer\\InstallTask', $task);
    }

    /**
     * @covers Mage\Task\Factory::get
     */
    public function testGetCustomTask()
    {
        $tasks = array(
            'Task\\MyFirstTask' => 'my-first-task',
            'MySecond\\Task' => 'my-second',
            'Mage\\Task\\BuiltIn\\CustomThirdTask' => 'custom-third',
            'Mage\\Task\\BuiltIn\\Custom\\FourthTask' => 'custom/fourth',
            'My\\Fifth\\TaskClass' => 'my/fifth/task-class',
        );

        foreach ($tasks as $taskClass => $taskName) {
            $alias = uniqid('DummyTask');
            $this->getMockBuilder('Mage\\Task\\AbstractTask')
                ->setConstructorArgs(array($this->config))
                ->setMockClassName($alias)
                ->getMock();

            /*
             * current workaround
             * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/134
             */
            class_alias($alias, $taskClass);

            $task = Factory::get($taskName, $this->config);
            $this->assertInstanceOf($taskClass, $task);
        }
    }

    /**
     * @covers Mage\Task\Factory::get
     */
    public function testGetWithOptionalParams()
    {
        $task = Factory::get('composer/install', $this->config, true, 'production');
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Composer\\InstallTask', $task);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The Task MyInconsistentTask must be an instance of Mage\Task\AbstractTask.
     * @covers Mage\Task\Factory::get
     */
    public function testGetInconsistentException()
    {
        $this->getMock('Task\\MyInconsistentTask');
        Factory::get('my-inconsistent-task', $this->config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Task "Unknowntask" not found.
     * @covers Mage\Task\Factory::get
     */
    public function testGetClassDoesNotExist()
    {
        Factory::get('unknowntask', $this->config);
    }
}
