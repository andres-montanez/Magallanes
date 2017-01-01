<?php
namespace Mage\Tests\Command\BuiltIn;

use Mage\Command\BuiltIn\VersionCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageTestApplication;
use Mage\Tests\Runtime\RuntimeMockup;
use Mage\Mage;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class VersionCommandTest extends TestCase
{
    public function testVersionOutput()
    {
        $application = new MageTestApplication();
        $application->add(new VersionCommand());
        
        /** @var AbstractCommand $command */
        $command = $application->find('version');
        $command->setRuntime(new RuntimeMockup());

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName()]);

        $output = trim($tester->getDisplay());
        $this->assertEquals(sprintf('Magallanes v%s [%s]', Mage::VERSION, Mage::CODENAME), $output);
    }
}
