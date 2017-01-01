<?php
namespace Mage\Tests\Command\BuiltIn;

use Mage\Command\BuiltIn\DeployCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageTestApplication;
use Mage\Tests\Runtime\RuntimeMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

class DeployCommandTest extends TestCase
{
    public function testDeploymentWithReleasesCommands()
    {
        $application = new MageTestApplication();
        $application->add(new DeployCommand());

        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(array(
                'environments' =>
                    array(
                        'test' =>
                            array(
                                'user' => 'tester',
                                'branch' => 'test',
                                'host_path' => '/var/www/test',
                                'releases' => 4,
                                'exclude' =>
                                    array(
                                        0 => 'vendor',
                                        1 => 'app/cache',
                                        2 => 'app/log',
                                        3 => 'web/app_dev.php',
                                    ),
                                'hosts' =>
                                    array(
                                        0 => 'testhost',
                                    ),
                                'pre-deploy' =>
                                    array(
                                        0 => 'git/update',
                                        1 => 'composer/install',
                                        2 => 'composer/generate-autoload',
                                    ),
                                'on-deploy' =>
                                    array(
                                        0 =>
                                            array(
                                                'symfony/cache-clear' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        1 =>
                                            array(
                                                'symfony/cache-warmup' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        2 =>
                                            array(
                                                'symfony/assets-install' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        3 =>
                                            array(
                                                'symfony/assetic-dump' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                    ),
                                'on-release' => null,
                                'post-release' => null,
                                'post-deploy' => null,
                            ),
                    ),
            )
        );

        $runtime->setReleaseId('20170101015120');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $command->setRuntime($runtime);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install --dev',
            4 => 'composer dumpautoload --optimize',
            5 => 'tar cfz /tmp/mageXYZ --exclude=.git --exclude=vendor --exclude=app/cache --exclude=app/log --exclude=web/app_dev.php ./',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"mkdir -p /var/www/test/releases/1234567890\\"',
            7 => 'scp -P 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no /tmp/mageXYZ tester@testhost:/var/www/test/releases/1234567890/mageXYZ',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& tar xfz mageXYZ\\"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm /var/www/test/releases/1234567890/mageXYZ\\"',
            10 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console cache:clear --env=dev \\"',
            11 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console cache:warmup --env=dev \\"',
            12 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assets:install --env=dev --symlink --relative web\\"',
            13 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assetic:dump --env=dev \\"',
            14 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test \\&\\& ln -snf releases/1234567890 current\\"',
            15 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"ls -1 /var/www/test/releases\\"',
            16 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015110\\"',
            17 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015111\\"',
            18 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015112\\"',
            19 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"rm -rf /var/www/test/releases/20170101015113\\"',
            20 => 'rm /tmp/mageXYZ',
            21 => 'git checkout master',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($ranCommands), count($testCase));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($ranCommands[$index], $command);
        }
    }

    public function testDeploymentWithoutReleasesCommands()
    {
        $application = new MageTestApplication();
        $application->add(new DeployCommand());

        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(array(
                'environments' =>
                    array(
                        'test' =>
                            array(
                                'user' => 'tester',
                                'branch' => 'test',
                                'host_path' => '/var/www/test',
                                'exclude' =>
                                    array(
                                        0 => 'vendor',
                                        1 => 'app/cache',
                                        2 => 'app/log',
                                        3 => 'web/app_dev.php',
                                    ),
                                'hosts' =>
                                    array(
                                        0 => 'testhost',
                                    ),
                                'pre-deploy' =>
                                    array(
                                        0 => 'git/update',
                                        1 => 'composer/install',
                                        2 => 'composer/generate-autoload',
                                    ),
                                'on-deploy' =>
                                    array(
                                        0 =>
                                            array(
                                                'symfony/cache-clear' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        1 =>
                                            array(
                                                'symfony/cache-warmup' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        2 =>
                                            array(
                                                'symfony/assets-install' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        3 =>
                                            array(
                                                'symfony/assetic-dump' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                    ),
                                'on-release' => null,
                                'post-release' => null,
                                'post-deploy' => null,
                            ),
                    ),
            )
        );

        $runtime->setReleaseId('1234567890');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $command->setRuntime($runtime);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git checkout test',
            2 => 'git pull',
            3 => 'composer install --dev',
            4 => 'composer dumpautoload --optimize',
            5 => 'rsync -avz --exclude=.git --exclude=vendor --exclude=app/cache --exclude=app/log --exclude=web/app_dev.php ./ tester@testhost:/var/www/test',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console cache:clear --env=dev \\"',
            7 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console cache:warmup --env=dev \\"',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assets:install --env=dev --symlink --relative web\\"',
            9 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assetic:dump --env=dev \\"',
            10 => 'git checkout master',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($ranCommands), count($testCase));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($ranCommands[$index], $command);
        }
    }

    public function testDeploymentWithSkippingTask()
    {
        $application = new MageTestApplication();
        $application->add(new DeployCommand());

        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(array(
                'environments' =>
                    array(
                        'test' =>
                            array(
                                'user' => 'tester',
                                'host_path' => '/var/www/test',
                                'branch' => 'master',
                                'exclude' =>
                                    array(
                                        0 => 'vendor',
                                        1 => 'app/cache',
                                        2 => 'app/log',
                                        3 => 'web/app_dev.php',
                                    ),
                                'hosts' =>
                                    array(
                                        0 => 'testhost',
                                    ),
                                'pre-deploy' =>
                                    array(
                                        0 => 'git/update',
                                        1 => 'composer/install',
                                        2 => 'composer/generate-autoload',
                                    ),
                                'on-deploy' =>
                                    array(
                                        0 =>
                                            array(
                                                'symfony/cache-clear' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        1 =>
                                            array(
                                                'symfony/cache-warmup' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        2 =>
                                            array(
                                                'symfony/assets-install' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                        3 =>
                                            array(
                                                'symfony/assetic-dump' =>
                                                    array(
                                                        'env' => 'dev',
                                                    ),
                                            ),
                                    ),
                                'on-release' => null,
                                'post-release' => null,
                                'post-deploy' => null,
                            ),
                    ),
            )
        );

        $runtime->setReleaseId('1234567890');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $command->setRuntime($runtime);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test']);

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'git branch | grep "*"',
            1 => 'git pull',
            2 => 'composer install --dev',
            3 => 'composer dumpautoload --optimize',
            4 => 'rsync -avz --exclude=.git --exclude=vendor --exclude=app/cache --exclude=app/log --exclude=web/app_dev.php ./ tester@testhost:/var/www/test',
            5 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console cache:clear --env=dev \\"',
            6 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console cache:warmup --env=dev \\"',
            7 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assets:install --env=dev --symlink --relative web\\"',
            8 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test/releases/1234567890 \\&\\& bin/console assetic:dump --env=dev \\"',
            9 => 'git branch | grep "*"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($ranCommands), count($testCase));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($ranCommands[$index], $command);
        }

        $this->assertTrue(strpos($tester->getDisplay(), 'SKIPPED') !== false);
    }
}
