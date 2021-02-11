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
use Mage\Tests\MageApplicationMockup;
use Mage\Command\AbstractCommand;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class DeployCommandMiscTasksTest extends TestCase
{
    public function testSymfonyEnvironmentConfiguration()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/symfony-envconf.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'rsync -e "ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" -avz --exclude=.git --exclude=./var/cache/* --exclude=./var/log/* --exclude=./web/app_dev.php ./ tester@testhost:/var/www/test',
            1 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && bin/console cache:warmup --env=testenv"',
            2 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && bin/console assets:install web --env=testenv --symlink --relative"',
            3 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && bin/console cache:pool:prune --env=testenv"',
            4 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && bin/console cache:pool:prune --env=prod"',
            5 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && bin/console cache:pool:clear main --env=testenv"',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost "cd /var/www/test && bin/console cache:pool:clear main --env=prod"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testComposerFlags()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/composer.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => '/usr/bin/composer.phar install --prefer-source',
            1 => '/usr/bin/composer.phar dump-autoload --no-scripts',
            2 => 'rsync -e "ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" -avz --exclude=.git --exclude=./var/cache/* --exclude=./var/log/* --exclude=./web/app_dev.php ./ tester@testhost:/var/www/test',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testGlobalExcludeFlags()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/global-exclude.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => '/usr/bin/composer.phar install --prefer-source',
            1 => '/usr/bin/composer.phar dump-autoload --no-scripts',
            2 => 'rsync -e "ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" -avz --exclude=.git --exclude=./var/cache/* --exclude=./var/log/* --exclude=./web/app_dev.php ./ tester@testhost:/var/www/test',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testComposerEnvFlags()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/composer-env.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => '/usr/foobar/composer install --prefer-source',
            1 => '/usr/foobar/composer dump-autoload --no-scripts',
            2 => 'rsync -e "ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" -avz --exclude=.git --exclude=./var/cache/* --exclude=./var/log/* --exclude=./web/app_dev.php ./ tester@testhost:/var/www/test',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testInvalidTaskName()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/invalid-task.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);
        $this->assertEquals(7, $tester->getStatusCode());
        $this->assertStringContainsString('Invalid task name "invalid/task"', $tester->getDisplay());
    }

    public function testBrokenGitBranch()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/broken-git-branch.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);

        $application->getRuntime()->forceFail('git branch | grep "*"');

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('Running [Git] Change Branch (broken-test) ... FAIL', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testBrokenGitCheckout()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/broken-git-branch.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);

        $application->getRuntime()->forceFail('git checkout broken-test');

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('Running [Git] Change Branch (broken-test) ... FAIL', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testBrokenGitUpdate()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../Resources/broken-git-branch.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);

        $application->getRuntime()->forceFail('git pull');

        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $this->assertStringContainsString('Running [Git] Update ... FAIL', $tester->getDisplay());
        $this->assertNotEquals(0, $tester->getStatusCode());
    }
}
