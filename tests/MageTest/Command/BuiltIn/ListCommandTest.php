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

    public function listEnvironmentsProvider()
    {
        return array(
            'normal' => array(
                'environmentFiles' => array(
                    'rc.yml',
                    'production.yml',
                    'local.yml'
                ),
                'expectedOutput' => "\tThese are your configured environments:\n"
                    . "\t\t* local\n"
                    . "\t\t* production\n"
                    . "\t\t* rc\n"
                    . "\t\n",
                'expectedExitCode' => 0
            ),
            'with_missing_yml_files' => array(
                'environmentFiles' => array(
                    'rc',
                    'production.yml'
                ),
                'expectedOutput' => "\tThese are your configured environments:\n"
                    . "\t\t* production\n"
                    . "\t\n",
                'expectedExitCode' => 0
            ),
            'with_no_yml_configs' => array(
                'environmentFiles' => array(
                    'rc.ini',
                    'production.txt'
                ),
                'expectedOutput' => "\tYou don't have any environment configured.\n\n",
                'expectedExitCode' => 220
            ),
            'with_no_configs' => array(
                'environmentFiles' => array(),
                'expectedOutput' => "\tYou don't have any environment configured.\n\n",
                'expectedExitCode' => 220
            )
        );
    }

    /**
     * @covers ::run
     * @covers ::listEnvironments
     * @dataProvider listEnvironmentsProvider
     */
    public function testListEnvironment($environmentFiles, $expectedOutput, $expectedExitCode)
    {
        $this->expectOutputString($expectedOutput);

        $this->scandirValueObj->setValue($environmentFiles);
        $this->mockInputArgument('environments');

        $actualExitCode = $this->listCommand->run();
        $this->assertEquals($expectedExitCode, $actualExitCode);
    }

    /**
     * @covers ::run
     */
    public function testRunWithInvalidCommand()
    {
        $expectedOutput = "\tThe Type of Elements to List is needed.\n\n";
        $this->expectOutputString($expectedOutput);

        $this->mockInputArgument('abc');

        $expectedExitCode = 221;
        $actualExitCode = $this->listCommand->run();
        $this->assertEquals($expectedExitCode, $actualExitCode);
    }

    private function mockInputArgument($argumentValue)
    {
        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->once())
            ->method('getArgument')
            ->with(1)
            ->willReturn($argumentValue);

        $this->listCommand->setConfig($configMock);
    }
}
