<?php

namespace MageTest\Command\BuiltIn;

use Mage\Command\BuiltIn\VersionCommand;
use Mage\Console;
use MageTest\TestHelper\BaseTest;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Mage\Command\BuiltIn\VersionCommand
 * @group Mage_Command_BuildIn_VersionCommand
 * @uses Mage\Console
 * @uses Mage\Console\Colors
 * @uses Mage\Command\AbstractCommand
 */
class VersionCommandTest extends BaseTest
{
    /**
     * @group 175
     * @covers ::__construct
     * @covers ::run()
     */
    public function testRun()
    {
        $this->setUpConsoleStatics();
        $command = new VersionCommand();
        $command->run();

        $this->expectOutputString('Running Magallanes version 2' . str_repeat(PHP_EOL, 2));
    }
}
