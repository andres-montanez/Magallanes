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

use Mage\Command\AbstractCommand;
use Mage\Command\BuiltIn\VersionCommand;
use Mage\Tests\MageApplicationMockup;
use Mage\Mage;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class VersionCommandTest extends TestCase
{
    public function testVersionOutput()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/basic.yml');
        
        /** @var AbstractCommand $command */
        $command = $application->find('version');
        $this->assertTrue($command instanceof VersionCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName()]);

        $output = trim($tester->getDisplay());
        $this->assertEquals(sprintf('Magallanes v%s [%s]', Mage::VERSION, Mage::CODENAME), $output);
    }
}
