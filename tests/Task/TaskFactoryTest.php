<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task;

use Mage\Command\AbstractCommand;
use Mage\Command\BuiltIn\DeployCommand;
use Mage\Task\TaskFactory;
use Mage\Runtime\Runtime;
use Mage\Runtime\Exception\RuntimeException;
use Exception;
use Mage\Tests\MageApplicationMockup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TaskFactoryTest extends TestCase
{
    public function testNonInstantiable()
    {
        $runtime = new Runtime();
        $factory = new TaskFactory($runtime);

        try {
            $factory->get('Traversable');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('Invalid task name "Traversable"', $exception->getMessage());
        }
    }

    public function testNotExtendingAbstractTask()
    {
        $runtime = new Runtime();
        $factory = new TaskFactory($runtime);

        try {
            $factory->get('stdClass');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('Invalid task name "stdClass"', $exception->getMessage());
        }
    }

    public function testPreRegisteredCustomTask()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../Resources/custom-task.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'production']);

        $this->assertStringContainsString('[Custom] Valid*', $tester->getDisplay());

        $ranCommands = $application->getRuntime()->getRanCommands();

        $testCase = array(
            0 => 'rsync -e "ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no" -avz --exclude=.git ./ app@webserver:/var/www/myapp',
            1 => 'ssh -p 22 -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no app@webserver "cd /var/www/myapp && echo \"custom-valid\""',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }

        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testPreRegisteredCustomTaskInvalidClass()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../Resources/custom-task-invalid-class.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'production']);

        $this->assertStringContainsString('Custom Task "Mage\Tests\Task\Custom\InvalidClass" does not exists.', $tester->getDisplay());

        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testPreRegisteredCustomTaskNonInstantiable()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../Resources/custom-task-not-instantiable.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'production']);

        $this->assertStringContainsString('Custom Task "Mage\Tests\Task\Custom\NotInstantiableTask" can not be instantiated.', $tester->getDisplay());

        $this->assertNotEquals(0, $tester->getStatusCode());
    }

    public function testPreRegisteredCustomTaskInvalidInheritance()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../Resources/custom-task-invalid-inheritance.yml');

        /** @var AbstractCommand $command */
        $command = $application->find('deploy');
        $this->assertTrue($command instanceof DeployCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName(), 'environment' => 'production']);

        $this->assertStringContainsString('Custom Task "Mage\Tests\Task\Custom\InvalidInheritanceTask" must inherit "Mage\Task\AbstractTask".', $tester->getDisplay());

        $this->assertNotEquals(0, $tester->getStatusCode());
    }
}
