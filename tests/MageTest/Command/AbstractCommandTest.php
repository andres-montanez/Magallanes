<?php

namespace MageTest\Command;

use Mage\Command\AbstractCommand;
use MageTest\TestHelper\BaseTest;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AbstractCommandTest
 * @package MageTest\Command
 * @author Jakub Turek <ja@kubaturek.pl>
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
        $this->doTestSetter($this->abstractCommand, 'config', $configMock);
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $configMock = $this->getMock('Mage\Config');
        $this->doTestGetter($this->abstractCommand, 'config', $configMock);
    }
}
