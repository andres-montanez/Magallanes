<?php

namespace MageTest\Command\BuiltIn;

use Mage\Command\BuiltIn\CompileCommand;
use MageTest\TestHelper\BaseTest;

/**
 * Class CompileCommandTest
 * @package MageTest\Command\BuiltIn
 * @coversDefaultClass Mage\Command\BuiltIn\CompileCommand
 * @uses Mage\Compiler
 */
class CompileCommandTest extends BaseTest
{
    /**
     * @var CompileCommand
     */
    private $compileCommand;

    /**
     * @before
     */
    public function before()
    {
        $this->compileCommand = new CompileCommand();
    }

    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $compileCommand = new CompileCommand();

        $compilerProperty = $this->getPropertyValue($compileCommand, 'compiler');
        $this->assertInstanceOf('Mage\Compiler', $compilerProperty);
    }

    /**
     * @covers ::setCompiler
     */
    public function testSetCompiler()
    {
        $compilerMock = $this->getMock('Mage\Compiler');
        $this->compileCommand->setCompiler($compilerMock);

        $compilerProperty = $this->getPropertyValue($this->compileCommand, 'compiler');
        $this->assertEquals($compilerMock, $compilerProperty);
    }
}
