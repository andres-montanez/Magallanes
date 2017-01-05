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

use Mage\Command\BuiltIn\DeployCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageApplicationMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

class DeployCommandWithReleasesTest extends TestCase
{
    public function testDeploymentWithReleasesCommands()
    {
        $application = new MageApplicationMockup();
        $application->configure(__DIR__ . '/../../Resources/testhost.yml');

        $application->getRuntime()->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install',
            4 => 'composer dumpautoload --optimize',
            5 => 'tar cfz /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"mkdir -p /var/www/test/releases/1234567890\\"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& tar xfz mageXYZ\\"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm /var/www/test/releases/1234567890/mageXYZ\\"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console cache:warmup --env=dev \\"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assets:install --env=dev --symlink --relative web\\"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assetic:dump --env=dev \\"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test \\&\\& ln -snf releases/1234567890 current\\"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"ls -1 /var/www/test/releases\\"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015110\\"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015111\\"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015112\\"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015113\\"',
            19 => 'rm /tmp/mageXYZ',
            20 => 'git checkout master',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertEquals(0, $tester->getStatusCode());
    }
}
