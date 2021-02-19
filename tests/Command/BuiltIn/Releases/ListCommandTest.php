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
use PHPUnit\Framework\TestCase;

class ListCommandTest extends TestCase
{
    public function testListReleasesCommands()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/testhost.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            1 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "readlink -f /var/www/test/current"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

    public function testListReleasesWithInvalidEnvironment()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/testhost.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'developers']);

        $this->assertNotEquals(0, $tester->getStatusCode());
        $this->assertStringContainsString('The environment "developers" does not exists.', $tester->getDisplay());
    }

    public function testListReleasesWithoutReleases()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/testhost-without-releases.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);
        $this->assertNotEquals(0, $tester->getStatusCode());
        $this->assertStringContainsString('Releases are not enabled', $tester->getDisplay());
    }

    public function testFailToGetCurrentRelease()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/testhost-fail-get-current.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);
        $this->assertNotEquals(0, $tester->getStatusCode());
        $this->assertStringContainsString('Unable to retrieve current release from host "host1"', $tester->getDisplay());
    }

    public function testNoReleasesAvailable()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/testhost-no-releases.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);
        $this->assertStringContainsString('No releases available on host host2', $tester->getDisplay());
    }

    public function testFailGetReleases()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/testhost-fail-get-releases.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);
        $this->assertNotEquals(0, $tester->getStatusCode());
        $this->assertStringContainsString('Unable to retrieve releases from host "host3"', $tester->getDisplay());
    }

    public function testNoHosts()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/testhost-no-hosts.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('releases:list');
        $this->assertTrue($command instanceof ListCommand);

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);
        $this->assertStringContainsString('No hosts defined', $tester->getDisplay());
    }
}
