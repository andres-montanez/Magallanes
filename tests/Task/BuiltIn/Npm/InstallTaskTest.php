<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Mage\Tests\Task\BuiltIn\Npm;

use Mage\Task\BuiltIn\Npm\InstallTask;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit_Framework_TestCase as TestCase;

class InstallTaskTest extends TestCase
{
    public function testInstallTask()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $task = new InstallTask();
        $task->setOptions(['flags' => '--flags']);
        $task->setRuntime($runtime);
        $this->assertEquals('[NPM] Install', $task->getDescription());

        $task->execute();

        $ranCommands = $runtime->getRanCommands();
        $testCase = array(
            0 => 'npm install --flags',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }
}
