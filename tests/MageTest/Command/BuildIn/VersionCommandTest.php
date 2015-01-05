<?php

namespace MageTest\Command\BuildIn;

use Mage\Command\BuiltIn\VersionCommand;
use Mage\Console;
use PHPUnit_Framework_TestCase;

/**
 * @group Mage_Command_BuildIn_VersionCommand
 */
class VersionCommandTest extends PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $this->workAroundStatic();
        $command = new VersionCommand();
        $command->run();

        $this->expectOutputString('Running Magallanes version 2' . str_repeat(PHP_EOL, 2));
    }

    /**
     * This is only needed as long as Console-class has static methods and properties
     */
    private function workAroundStatic()
    {
        $refClass = new \ReflectionClass('Mage\Console');

        $refProperty = $refClass->getProperty('logEnabled');
        $refProperty->setAccessible(true);
        $refProperty->setValue(false);

        $config = $this->getMock('Mage\Config');
        $config->expects($this->once())
            ->method('getParameter')
            ->will($this->returnValue(true));

        $refProperty = $refClass->getProperty('config');
        $refProperty->setAccessible(true);
        $refProperty->setValue($config);
    }
}
