<?php

namespace MageTest\Task;

use Mage\Task\Factory;
use PHPUnit_Framework_TestCase;

/**
 * @group MageTest_Task
 * @group MageTest_Task_Factory
 * @uses Mage\Task\AbstractTask
 * @uses Doctrine\Instantiator\Instantiator
 * @covers Mage\Task\Factory
 *
 * @group issue-176
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $config;

    protected function setUp()
    {
        $this->config = $this->getMock('Mage\\Config');
    }

    public function testGet()
    {
        $task = Factory::get('composer/install', $this->config);
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Composer\\InstallTask', $task);
    }

    public function testGetTaskDataIsArray()
    {
        $taskData = array(
            'name' => 'composer/install',
            'parameters' => array(),
        );

        $task = Factory::get($taskData, $this->config);
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Composer\\InstallTask', $task);
    }

    public function testGetCustomTask()
    {
        $this->getMockBuilder('Mage\\Task\\AbstractTask')
            ->disableOriginalConstructor()
            ->setMockClassName('MyTask')
            ->getMock();

        /**
         * current workaround
         * @link https://github.com/sebastianbergmann/phpunit-mock-objects/issues/134
         */
        class_alias('MyTask', 'Task\\MyTask');

        $task = Factory::get('my-task', $this->config);
        $this->assertInstanceOf('Task\\MyTask', $task);
    }

    public function testGetWithOptionalParams()
    {
        $task = Factory::get('composer/install', $this->config, true, 'production');
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Composer\\InstallTask', $task);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The Task MyInconsistentTask must be an instance of Mage\Task\AbstractTask.
     */
    public function testGetInconsistentException()
    {
        $this->getMock('Task\\MyInconsistentTask');
        Factory::get('my-inconsistent-task', $this->config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Task "Unknowntask" not found.
     */
    public function testGetClassDoesNotExist()
    {
        Factory::get('unknowntask', $this->config);
    }
}
