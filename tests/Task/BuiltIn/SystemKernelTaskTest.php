<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Halis Duraki <duraki.halis@nsoft.ba>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task\BuiltIn;

use Mage\Task\Exception\ErrorException;
use Mage\Task\BuiltIn\System\Exec\ExecuteSystem;
use Exception;
use Mage\Tests\Runtime\RuntimeMockup;
use PHPUnit_Framework_TestCase as TestCase;

class SystemKernelTaskTest extends TestCase
{

    public function testExecuteSystem()
    {
        $runtime = new RuntimeMockup();
        $runtime->setConfiguration(['environments' => ['test' => []]]);
        $runtime->setEnvironment('test');

        $exec = new ExecuteSystem();
        $exec->setOptions(['exec' => 'cat', 'arg' => '/etc/passwd']);
        $exec->setRuntime($runtime);

        $this->assertContains('cat', $exec->getDescription());
        $exec->execute();

        $ranCommands = $runtime->getRanCommands();

        $testCase = array(
            0 => 'cat /etc/passwd',
        );

        // Check total of Executed Commands
        $this->assertEquals(count($testCase), count($ranCommands));

        // Check Generated Commands
        foreach ($testCase as $index => $command) {
            $this->assertEquals($command, $ranCommands[$index]);
        }
    }

}