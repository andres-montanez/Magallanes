<?php
namespace MageTest\Task\BuiltIn\Symfony2;

use Mage\Task\Factory;
use PHPUnit_Framework_TestCase;

class ApplyFaclsTaskTest extends PHPUnit_Framework_TestCase
{
    private $config;

    protected function setUp()
    {
        $this->config = $this->getMock('Mage\\Config');
    }

    /**
     * @covers Mage\Task\Factory::get
     */
    public function testGet()
    {
        $task = Factory::get('symfony2/apply-facls', $this->config);
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Symfony2\\ApplyFaclsTask', $task);
    }

    /**
     * @covers Mage\Task\Factory::get
     */
    public function testGetTaskDataIsArray()
    {
        $taskData = array(
            'name' => 'symfony2/apply-facls',
            'parameters' => array(
                'httpuser'  => 'www-data',
                'localuser' => 'deployer',
                'folders' => array('var', 'web')
            ),
        );

        $task = Factory::get($taskData, $this->config);
        $this->assertInstanceOf('\\Mage\\Task\\BuiltIn\\Symfony2\\ApplyFaclsTask', $task);
    }

    /**
     * @expectedException \Mage\Task\SkipException
     * @expectedExceptionMessage Parameter httpuser not set.
     * @covers Mage\Task\BuiltIn\Symfony2\ApplyFaclsTask::run
     */
    public function test_Run_WithNoHttpUserParameter_Expect_throw_SkipException()
    {
        // Arrange
        $taskData = array(
            'name' => 'symfony2/apply-facls',
            'parameters' => array(
                'httpuser'  => '',
                'localuser' => 'deployer',
                'folders' => array('var', 'web')
            ),
        );
        // Act
        $task = Factory::get($taskData, $this->config);
        $task->run();
    }
}
