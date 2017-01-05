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

use Mage\Command\BuiltIn\Releases\ListCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageApplicationMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

class ListCommandTest extends TestCase
{
    public function testListReleasesCommands()
    {
        $application = new MageApplicationMockup();
        $application->configure(__DIR__ . '/../../../Resources/testhost.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"ls -1 /var/www/test/releases\\"',
            1 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"readlink -f /var/www/test/current\\"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }
}
