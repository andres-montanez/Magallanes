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
use Mage\Tests\MageApplicationWindowsMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class DeployCommandWithReleasesTest extends TestCase
{
    public function testDeploymentWithReleasesCommands()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithReleasesCommandsWithTag()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost.yml');

        $application->getRuntime()->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test', '--tag' => 'v1.0.0']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout v1.0.0',
            2 => 'composer install --optimize-autoloader',
            3 => 'composer dump-autoload --optimize',
            4 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            5 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            6 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            7 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
            18 => 'rm /tmp/mageXYZ',
            19 => 'git checkout master',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentWithReleasesCommandsCustomSymlink()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-custom-symlink.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 prod"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithReleasesWithPortCommands()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-with-port.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=prod"',
            11 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=prod --symlink --relative"',
            12 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=prod"',
            13 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 202 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithReleasesWithFromCommands()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-with-from.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./dist',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithReleasesWithFromCommandsOnWindows()
    {
        $application = new MageApplicationWindowsMockup(__DIR__ . '/../../Resources/testhost-with-from.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar --force-local -c -z -p -f /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./dist',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithReleasesWithFromCommandsWithDirectoryCopy()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-with-from-copy-directory.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" -C ./dist ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithReleasesWithTimeout()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-with-release-timeout.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithReleasesWithSCPFlags()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-with-release-scp-flags.yml');

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
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -p -l 1000 /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
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

    public function testDeploymentWithoutReleasesTarPrepare()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-force-tar1.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('This task is only available with releases enabled', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentWithoutReleasesTarCopy()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-force-tar2.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('This task is only available with releases enabled', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentWithoutReleasesTarCleanup()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-force-tar3.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('This task is only available with releases enabled', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentFailCopyCommands()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-fail-copy-tar.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('Copying files with Tar ... FAIL', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentWithoutReleasesForceRelease()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost-force-release.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('This task is only available with releases enabled', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentFailToExtract()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost.yml');

        $application->getRuntime()->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $application->getRuntime()->forceFail('ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"');

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertStringContainsString('Running [Deploy] Copying files with Tar ... FAIL', $tester->getDisplay());
        $this->assertStringContainsString('Stage "On Deploy" did not finished successfully, halting command.', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentFailToCopy()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost.yml');

        $application->getRuntime()->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $application->getRuntime()->forceFail('scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ');

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertStringContainsString('Running [Deploy] Copying files with Tar ... FAIL', $tester->getDisplay());
        $this->assertStringContainsString('Stage "On Deploy" did not finished successfully, halting command.', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentFailCleanup()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost.yml');

        $application->getRuntime()->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $application->getRuntime()->forceFail('rm /tmp/mageXYZ');

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015111"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015112"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015113"',
            19 => 'rm /tmp/mageXYZ',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertStringContainsString('Running [Deploy] Cleanup Tar file ... FAIL', $tester->getDisplay());
        $this->assertStringContainsString('Stage "Post Deploy" did not finished successfully, halting command.', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentFailCleanupReleases()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost.yml');

        $application->getRuntime()->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $application->getRuntime()->forceFail('ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"');

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm -rf /var/www/test/releases/20170101015110"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertStringContainsString('Running [Release] Cleaning up old Releases ... FAIL', $tester->getDisplay());
        $this->assertStringContainsString('Stage "Post Release" did not finished successfully, halting command.', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testDeploymentFailMidway()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/testhost.yml');

        $application->getRuntime()->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $application->getRuntime()->forceFail('ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"');

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install --optimize-autoloader',
            4 => 'composer dump-autoload --optimize',
            5 => 'tar cfzp /tmp/mageXYZ --exclude=".git" --exclude="./var/cache/*" --exclude="./var/log/*" --exclude="./web/app_dev.php" ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "mkdir -p /var/www/test/releases/1234567890"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && tar xfzop mageXYZ"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "rm /var/www/test/releases/1234567890/mageXYZ"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:warmup --env=dev"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console assets:install web --env=dev --symlink --relative"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test/releases/1234567890 && bin/console cache:pool:prune --env=dev"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && ln -snf releases/1234567890 current"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "ls -1 /var/www/test/releases"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertStringContainsString('Running [Release] Cleaning up old Releases ... FAIL', $tester->getDisplay());
        $this->assertStringContainsString('Stage "Post Release" did not finished successfully, halting command.', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }
}
