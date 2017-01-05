<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Command\BuiltIn\Releases;

use Mage\Command\BuiltIn\Releases\RollbackCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageApplicationMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

class RollbackCommandTest extends TestCase
{
    public function testRollbackReleaseCommands()
    {
        $application = new MageApplicationMockup();
        $application->configure(__DIR__ . '/../../../Resources/testhost.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:rollback');
        $this->assertTrue($command instanceof RollbackCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test', 'release' => '20170101015115']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"ls -1 /var/www/test/releases\\"',
            1 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test \\&\\& ln -snf releases/20170101015115 current\\"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }
}
