<?php

namespace MageTest\Task;

use Mage\Task\Factory;
use PHPUnit_Framework_TestCase;

require_once(__DIR__ . '/../../Dummies/Task/MyTask.php');
require_once(__DIR__ . '/../../Dummies/Task/MyInconsistentTask.php');

/**
 * @group MageTest_Task
 * @group MageTest_Task_Factory
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $config;

    protected function setUp()
    {
        $this->config = $this->getMock('Mage\Config');
    }

    /**
     * @dataProvider taskDataProvider
     */
    public function testGet($taskData)
    {
        $task = Factory::get($taskData, $this->config);
        $this->assertInstanceOf('\\Mage\\Task\\AbstractTask', $task);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The Task MyInconsistentTask must be an instance of Mage\Task\AbstractTask.
     */
    public function testGetInconsistentException()
    {
        Factory::get('my-inconsistent-task', $this->config);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Task "Notknowntask" not found.
     */
    public function testGetClassDoesNotExist()
    {
        Factory::get('notknowntask', $this->config);
    }

    /**
     * Only build in tasks contains a slash
     *
     * @return array
     */
    public function taskDataProvider()
    {
        return array(
            array(
                array(
                    'name' => 'my-task',
                    'parameters' => array(),
                )
            ),
            array('my-task'),
            array(
                array(
                    'name' => 'composer/install',
                    'parameters' => array(),
                )
            ),
            array('composer/install'),
            array(
                array(
                    'name' => 'magento/clear-cache',
                    'parameters' => array(),
                )
            ),
            array('magento/clear-cache'),
        );
    }
}
 