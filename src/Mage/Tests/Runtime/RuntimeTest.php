<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Runtime;

use Mage\Runtime\Runtime;
use Mage\Runtime\Exception\InvalidEnvironmentException;
use Exception;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Process;

class RuntimeTest extends TestCase
{
    public function testReleaseIdGeneration()
    {
        $releaseId = date('YmdHis');

        $runtime = new Runtime();
        $runtime->generateReleaseId();

        $this->assertEquals($releaseId, $runtime->getReleaseId());
    }

    public function testEmptyEnvironmentConfig()
    {
        $runtime = new Runtime();
        $config = $runtime->getEnvironmentConfig();

        $this->assertTrue(is_array($config));
        $this->assertEquals(0, count($config));
    }

    public function testInvalidEnvironments()
    {
        try {
            $runtime = new Runtime();
            $runtime->setEnvironment('invalid');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof InvalidEnvironmentException);
        }

        try {
            $runtime = new Runtime();
            $runtime->setConfiguration(['environments' => ['valid' => []]]);
            $runtime->setEnvironment('invalid');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof InvalidEnvironmentException);
        }
    }

    public function testTempFile()
    {
        $runtime = new Runtime();
        $tempFile = $runtime->getTempFile();

        $this->assertNotEquals('', $tempFile);
        $this->assertTrue(file_exists($tempFile));
        $this->assertEquals(0, filesize($tempFile));
    }

    public function testSSHConfigUndefinedOptions()
    {
        $runtime = new Runtime();
        $sshConfig = $runtime->getSSHConfig();

        $this->assertTrue(is_array($sshConfig));

        $this->assertTrue(array_key_exists('port', $sshConfig));
        $this->assertEquals('22', $sshConfig['port']);

        $this->assertTrue(array_key_exists('flags', $sshConfig));
        $this->assertEquals('-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no', $sshConfig['flags']);
    }

    public function testSSHConfigEmptyOptions()
    {
        $runtime = new Runtime();
        $runtime->setConfiguration(['environments' => ['test' => ['ssh' => []]]]);
        $runtime->setEnvironment('test');
        $sshConfig = $runtime->getSSHConfig();

        $this->assertTrue(is_array($sshConfig));

        $this->assertTrue(array_key_exists('port', $sshConfig));
        $this->assertEquals('22', $sshConfig['port']);

        $this->assertTrue(array_key_exists('flags', $sshConfig));
        $this->assertEquals('-q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no', $sshConfig['flags']);
    }

    public function testSSHConfigCustomOptions()
    {
        $runtime = new Runtime();
        $runtime->setConfiguration(['environments' => ['test' => ['ssh' => ['port' => '2222', 'flags' => '-q']]]]);
        $runtime->setEnvironment('test');
        $sshConfig = $runtime->getSSHConfig();

        $this->assertTrue(is_array($sshConfig));

        $this->assertTrue(array_key_exists('port', $sshConfig));
        $this->assertEquals('2222', $sshConfig['port']);

        $this->assertTrue(array_key_exists('flags', $sshConfig));
        $this->assertEquals('-q', $sshConfig['flags']);
    }

    public function testLogger()
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $runtime = new Runtime();
        $runtime->setLogger($logger);

        $runtime->log('Test Message', LogLevel::INFO);

        $this->assertTrue($handler->hasInfoRecords());
        $this->assertTrue($handler->hasInfo('Test Message'));
    }

    public function testLocalCommand()
    {
        $runtime = new Runtime();

        /** @var Process $process */
        $process = $runtime->runLocalCommand('date +%s');
        $this->assertTrue($process->isSuccessful());
        $this->assertEquals(time(), trim($process->getOutput()));

        /** @var Process $process */
        $process = $runtime->runLocalCommand('false');
        $this->assertFalse($process->isSuccessful());
    }
}
