<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Command\BuiltIn\Config;

use Mage\Command\BuiltIn\Config\EnvironmentsCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageTestApplication;
use Mage\Tests\Runtime\RuntimeMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit_Framework_TestCase as TestCase;

class EnvironmentsCommandTest extends TestCase
{
    public function testConfigDumpTermination()
    {
        $application = new MageTestApplication();
        $application->add(new EnvironmentsCommand());

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
        $command = $application->find('config:environments');
        $command->setRuntime($runtime);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName()]);

        $this->assertEquals(0, $tester->getStatusCode());
    }
}
