<?php

namespace MageTest\Command;
use Mage\Command\AbstractCommand;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AbstractCommandTest
 * @package MageTest\Command
 * @coversDefaultClass Mage\Command\AbstractCommand
 */
class AbstractCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractCommand|PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractCommand;

    /**
     * @before
     */
    public function before()
    {
        $this->abstractCommand = $this->getMockForAbstractClass('Mage\Command\AbstractCommand');
    }

    /**
     * @covers ::setConfig
     */
    public function testSetConfig()
    {
        $configMock = $this->getMock('Mage\Config');
        $this->abstractCommand->setConfig($configMock);

        $configProperty = new \ReflectionProperty($this->abstractCommand, 'config');
        $configProperty->setAccessible(true);
        $configValue = $configProperty->getValue($this->abstractCommand);

        $this->assertEquals($configMock, $configValue);
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $configMock = $this->getMock('Mage\Config');

        $configProperty = new \ReflectionProperty($this->abstractCommand, 'config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($this->abstractCommand, $configMock);

        $actual = $this->abstractCommand->getConfig();
        $this->assertEquals($configMock, $actual);
    }
}
