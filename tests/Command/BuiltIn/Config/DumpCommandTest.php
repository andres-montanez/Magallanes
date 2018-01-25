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

use Mage\Command\BuiltIn\Config\DumpCommand;
use Mage\Command\AbstractCommand;
use Mage\Tests\MageApplicationMockup;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class DumpCommandTest extends TestCase
{
    public function testConfigDumpTermination()
    {
        $application = new MageApplicationMockup(__DIR__ . '/../../../Resources/basic.yml');
        
        /** @var AbstractCommand $command */
        $command = $application->find('config:dump');
        $this->assertTrue($command instanceof DumpCommand);

        $tester = new CommandTester($command);
        $tester->execute(['command' => $command->getName()]);

        $this->assertEquals(0, $tester->getStatusCode());
    }
}
