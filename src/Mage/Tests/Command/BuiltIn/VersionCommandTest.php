<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Command\BuiltIn;

use Mage\Command\BuiltIn\VersionCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageTestApplication;
use Mage\Tests\Runtime\RuntimeMockup;
use Mage\Mage;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

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
