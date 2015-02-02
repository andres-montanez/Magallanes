<?php

namespace MageTest\Task;

use Mage\Task\Factory;
use PHPUnit_Framework_TestCase;

/**
 * @group Mage_Task
 * @coversDefaultClass Mage\Task\Factory
 */
class ColorsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/MyTaskExample.php';
    }

    public function testLoadFromExternalNamespace()
    {
        $factory = new Factory;

        $configMock = $this
            ->getMockBuilder('Mage\Config')
            ->getMock();

        $myTask = $factory->get('My\Task\Example', $configMock);

        $this->assertInstanceOf('My\Task\Example', $myTask);

    }
}

