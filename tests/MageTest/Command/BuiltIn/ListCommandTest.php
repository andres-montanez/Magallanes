<?php

namespace MageTest\Command\BuiltIn;

use Mage\Command\BuiltIn\ListCommand;
use MageTest\TestHelper\BaseTest;
use malkusch\phpmock\FixedValueFunction;
use malkusch\phpmock\Mock;
use malkusch\phpmock\MockBuilder;

/**
 * Class ListCommandTest
 * @package MageTest\Command\BuiltIn
 * @coversDefaultClass Mage\Command\BuiltIn\ListCommand
 * @uses malkusch\phpmock\Mock
 * @uses malkusch\phpmock\MockBuilder
 * @uses malkusch\phpmock\FixedValueFunction
 * @uses Mage\Console\Colors
 * @uses Mage\Console
 * @uses Mage\Command\AbstractCommand
 */
class ListCommandTest extends BaseTest
{
    /**
     * @var ListCommand
     */
    private $listCommand;

    /**
     * @var Mock
     */
    private $scandirMock;

    /**
     * @var FixedValueFunction
     */
    private $scandirValueObj;

    /**
     * @before
     */
    public function before()
    {
        $this->listCommand = new ListCommand();

        $this->scandirValueObj = new FixedValueFunction();
        $mockBuilder = new MockBuilder();
        $this->scandirMock = $mockBuilder->setNamespace('Mage\Command\BuiltIn')
            ->setName("scandir")
            ->setCallableProvider($this->scandirValueObj)
            ->build();
        $this->scandirMock->disable();
        $this->scandirMock->enable();
    }

    /**
     * Disable logging to log file and turn off colors
     *
     * @before
     */
    public function setUpConsoleStatics()
    {
        $consoleReflection = new \ReflectionClass('Mage\Console');
        $logEnableProperty = $consoleReflection->getProperty('logEnabled');
        $logEnableProperty->setAccessible(true);
        $logEnableProperty->setValue(false);

        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->atLeastOnce())
            ->method('getParameter')
            ->with('no-color')
            ->willReturn(true);

        $configProperty = $consoleReflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($configMock);
    }

    /**
     * @covers ::run
     * @covers ::listEnvironments
     */
    public function testListEnvironment()
    {
        $expectedOutput = <<<OUTPUT
\tThese are your configured environments:
\t\t* local
\t\t* production
\t\t* rc
\t\n
OUTPUT;
        $this->expectOutputString($expectedOutput);

        $environmentsFiles = [
            'rc.yml',
            'production.yml',
            'local.yml'
        ];

        $this->scandirValueObj->setValue($environmentsFiles);

        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->once())
            ->method('getArgument')
            ->with(1)
            ->willReturn('environments');
        $this->listCommand->setConfig($configMock);

        $this->listCommand->run();
    }

    /**
     * @covers ::run
     * @covers ::listEnvironments
     */
    public function testListEnvironmentWithNoEnvironments()
    {
        $expectedOutput = "\tYou don't have any environment configured.\n\n";
        $this->expectOutputString($expectedOutput);

        $this->scandirValueObj->setValue([]);

        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->once())
            ->method('getArgument')
            ->with(1)
            ->willReturn('environments');
        $this->listCommand->setConfig($configMock);
        $this->listCommand->run();
    }

    /**
     * @covers ::run
     */
    public function testRunWithInvalidCommand()
    {
        $expectedOutput = "\tThe Type of Elements to List is needed.\n\n";
        $this->expectOutputString($expectedOutput);

        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->once())
            ->method('getArgument')
            ->with(1)
            ->willReturn('abc');
        $this->listCommand->setConfig($configMock);
        $this->listCommand->run();
    }
}
