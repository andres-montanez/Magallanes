<?php
namespace Mage\Tests\Command\BuiltIn\Releases;

use Mage\Command\BuiltIn\Releases\RollbackCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageTestApplication;
use Mage\Tests\Runtime\RuntimeMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

class RollbackCommandTest extends TestCase
{
    public function testRollbackReleaseCommands()
    {
        $application = new MageTestApplication();
        $application->add(new RollbackCommand());

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

        /** @var AbstractCommand $command */
        $command = $application->find('releases:rollback');
        $command->setRuntime($runtime);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'test', 'release' => '20170101015115']);

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"ls -1 /var/www/test/releases\\"',
            1 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no tester@testhost sh -c \\"cd /var/www/test \\&\\& ln -snf releases/20170101015115 current\\"',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($ranCommands), count($testCase));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($ranCommands[$index], $command);
        }
    }
}
