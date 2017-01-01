<?php
namespace Mage\Tests\Command\BuiltIn\Config;

use Mage\Command\BuiltIn\Config\DumpCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageTestApplication;
use Mage\Tests\Runtime\RuntimeMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

class DumpCommandTest extends TestCase
{
    public function testConfigDumpTermination()
    {
        $application = new MageTestApplication();
        $application->add(new DumpCommand());
        
        /** @var AbstractCommand $command */
        $command = $application->find('config:dump');
        $command->setRuntime(new RuntimeMockup());

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName()]);

        $this->assertEquals($tester->getStatusCode(), 0);
    }
}
