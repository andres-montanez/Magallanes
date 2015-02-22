<?php

namespace MageTest\Command\BuiltIn;

use Mage\Command\BuiltIn\CompileCommand;
use MageTest\TestHelper\BaseTest;
use malkusch\phpmock\FixedValueFunction;
use malkusch\phpmock\Mock;
use malkusch\phpmock\MockBuilder;

/**
 * Class CompileCommandTest
 * @package MageTest\Command\BuiltIn
 * @coversDefaultClass Mage\Command\BuiltIn\CompileCommand
 * @uses Mage\Compiler
 * @uses malkusch\phpmock\FixedValueFunction
 * @uses malkusch\phpmock\Mock
 * @uses malkusch\phpmock\MockBuilder
 * @uses Mage\Console
 * @uses Mage\Console\Colors
 */
class CompileCommandTest extends BaseTest
{
    /**
     * @var CompileCommand
     */
    private $compileCommand;

    /**
     * @var FixedValueFunction
     */
    private $iniGetValue;

    /**
     * @before
     */
    public function before()
    {
        $this->compileCommand = new CompileCommand();

        $this->iniGetValue = new FixedValueFunction();
        $mockBuilder = new MockBuilder();
        $iniGetMock = $mockBuilder->setNamespace('Mage\Command\BuiltIn')
            ->setName("ini_get")
            ->setCallableProvider($this->iniGetValue)
            ->build();
        $iniGetMock->disable();
        $iniGetMock->enable();

        $this->setUpConsoleStatics();
    }

    /**
     * @covers ::__construct
     * @covers ::setCompiler
     */
    public function testConstruct()
    {
        $compilerProperty = $this->getPropertyValue($this->compileCommand, 'compiler');
        $this->assertInstanceOf('Mage\Compiler', $compilerProperty);
    }

    /**
     * @covers ::__construct
     * @covers ::setCompiler
     */
    public function testSetCompiler()
    {
        $compilerMock = $this->getMock('Mage\Compiler');
        $this->compileCommand->setCompiler($compilerMock);

        $compilerProperty = $this->getPropertyValue($this->compileCommand, 'compiler');
        $this->assertEquals($compilerMock, $compilerProperty);
    }

    /**
     * @covers ::__construct
     * @covers ::setCompiler
     * @covers ::run
     */
    public function testRun()
    {
        $expectedOutput = "mage.phar compiled successfully\n\n";
        $expectedExitCode = 0;
        $this->expectOutputString($expectedOutput);

        $compilerMock = $this->getMock('Mage\Compiler');
        $compilerMock->expects($this->once())
            ->method('compile');

        $this->iniGetValue->setValue(false);
        $this->compileCommand->setCompiler($compilerMock);
        $actualExitCode = $this->compileCommand->run();

        $this->assertEquals($expectedExitCode, $actualExitCode);
    }

    /**
     * @covers ::__construct
     * @covers ::setCompiler
     * @covers ::run
     */
    public function testRunWhenPharReadonlyEnabled()
    {
        $expectedOutput = "\tThe php.ini variable phar.readonly must be Off.\n\n";
        $expectedExitCode = 200;
        $this->expectOutputString($expectedOutput);
        $this->iniGetValue->setValue(true);

        $actualExitCode = $this->compileCommand->run();

        $this->assertEquals($expectedExitCode, $actualExitCode);
    }
}
