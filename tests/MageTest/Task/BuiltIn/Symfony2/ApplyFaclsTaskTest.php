<?php
namespace MageTest\Task\BuiltIn\Symfony2;

use Mage\Config;
use Mage\Task\BuiltIn\Symfony2\ApplyFaclsTask;
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
        $config = new Config();
        $config->addParameter('httpuser', '');
        $task = new ApplyFaclsTask($config);
        // Act
        $task->run();
    }

    /**
     * @group unit
     * @expectedException \Mage\Task\SkipException
     * @expectedExceptionMessage Parameter localuser not set.
     * @covers Mage\Task\BuiltIn\Symfony2\ApplyFaclsTask::run
     */
    public function test_run_withNoLocalUserParameter_trow_SkipException()
    {
        // Arrange
        $config = new Config();
        $config->addParameter('httpuser', 'www-data');
        $config->addParameter('localuser', '');
        $task = new ApplyFaclsTask($config);
        // Act
        $task->run();
    }

    public function provider_createFaclsCommand()
    {
        return array(
            'no facls options' => array(
                '', 'www-data', 'deployer', 'var', 'setfacl  -m u:www-data:rwX -m u:deployer:rwX var'
            ),
            'with facls options' => array(
                '-dR', 'www-data', 'deployer', 'var', 'setfacl -dR -m u:www-data:rwX -m u:deployer:rwX var'
            )
        );
    }

    /**
     * @group unit
     * @dataProvider provider_createFaclsCommand
     * @covers       Mage\Task\BuiltIn\Symfony2\ApplyFaclsTask::createFaclCommand
     *
     * @param string $faclOptions
     * @param string $httpUser
     * @param string $localUser
     * @param string $folder
     * @param string $expectedCommand
     */
    public function test_createFaclsCommand($faclOptions, $httpUser, $localUser, $folder, $expectedCommand)
    {
        // Arrange
        $task = new ApplyFaclsTask($this->config);
        // Act
        $command = $task->createFaclCommand($faclOptions, $httpUser, $localUser, $folder);
        // Assert
        $this->assertEquals($expectedCommand, $command);
    }
}
