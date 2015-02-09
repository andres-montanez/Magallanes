<?php

namespace MageTest\Task;

use Mage\Task\Factory;
use PHPUnit_Framework_TestCase;

/**
 * @group Mage_Task
 * @coversDefaultClass Mage\Task\Factory
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->configMock = $this
            ->getMock('Mage\Config');

        require_once __DIR__ . '/MyTaskExample.php';
    }

    /**
     * @covers Mage\Task\Factory
     */
    public function testLoadFromExternalNamespace()
    {
        $factory = new Factory;

        $myTask = $factory->get('My\Task\Example', $this->configMock);

        $this->assertInstanceOf('My\Task\Example', $myTask);

    }

    /**
     * @covers Mage\Task\Factory
     */
    public function testLoadClassWithoutNamespace()
    {
        $factory = new Factory();

        $mock = $this
            ->getMockBuilder('Mage\Task\AbstractTask')
            ->setMockClassName('MyMageCustomTask')
            ->disableOriginalConstructor()
            ->getMock();

        $task = $factory->get('MyMageCustomTask', $this->configMock);

        $this->assertInstanceof('MyMageCustomTask', $task);
    }
}

