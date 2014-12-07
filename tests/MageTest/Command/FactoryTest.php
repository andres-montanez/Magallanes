<?php

namespace MageTest\Command;

use Mage\Command\Factory;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_Constraint_IsInstanceOf;

require_once(__DIR__ . '/../../Dummies/Command/MyCommand.php');
require_once(__DIR__ . '/../../Dummies/Command/MyInconsistentCommand.php');

/**
 * @group Mage_Command
 * @group Mage_Command_Factory
 *
 * @todo test case for naming convention
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{
    private $config;

    protected function setUp()
    {
        $this->config = $this->getMock('Mage\Config');
    }

    public function testGet()
    {
        $command = Factory::get('add', $this->config);
        $this->assertInstanceOf('Mage\\Command\\BuiltIn\\AddCommand', $command);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetClassNotFoundException()
    {
        $command = Factory::get('commanddoesntexist', $this->config);
    }

    public function testGetCustomCommand()
    {
        $command = Factory::get('my-command', $this->config);
        $this->assertInstanceOf('Command\\MyCommand', $command);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The command MyInconsistentCommand must be an instance of Mage\Command\AbstractCommand.
     */
    public function testGetInconsistencyException()
    {
        $command = Factory::get('my-inconsistent-command', $this->config);
    }
}
