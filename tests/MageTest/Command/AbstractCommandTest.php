<?php

namespace MageTest\Command;
use Mage\Command\AbstractCommand;
use MageTest\TestHelper\BaseTest;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AbstractCommandTest
 * @package MageTest\Command
 * @coversDefaultClass Mage\Command\AbstractCommand
 */
class AbstractCommandTest extends BaseTest
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

        $actual = $this->getPropertyValue($this->abstractCommand, 'config');
        $this->assertEquals($configMock, $actual);
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $configMock = $this->getMock('Mage\Config');
        $this->setPropertyValue($this->abstractCommand, 'config', $configMock);

        $actual = $this->abstractCommand->getConfig();
        $this->assertEquals($configMock, $actual);
    }
}
