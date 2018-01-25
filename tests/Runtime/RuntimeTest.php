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

use Mage\Runtime\Exception\RuntimeException;
use Mage\Runtime\Runtime;
use Exception;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Process;
use DateTime;

class RuntimeTest extends TestCase
{
    public function testReleaseIdGeneration()
    {
        // Given that this is a time based operation, lets conform that at least the format is right
        // and the time diff is less than 2 seconds

        $now = new DateTime();

        $runtime = new Runtime();
        $runtime->generateReleaseId();
        $releaseId = $runtime->getReleaseId();

        $releaseDate = DateTime::createFromFormat('YmdHis', $releaseId);
        $this->assertTrue($releaseDate instanceof DateTime);

        $dateDiff = $releaseDate->diff($now);
        $this->assertLessThanOrEqual(2, $dateDiff->s);
    }

    public function testEnvOptionWithInvalidEnvironment()
    {
        $runtime = new Runtime();
        $runtime->setConfiguration(['environments' => ['invalid' => []]]);
        $runtime->setEnvironment('invalid');
        $runtime->setConfiguration(['environments' => []]);

        $controlValue = time();
        $returnedValue = $runtime->getEnvOption('test', $controlValue);

        $this->assertEquals($controlValue, $returnedValue);
    }

    public function testNoConfigWithEmptyTasks()
    {
        $runtime = new Runtime();
        $tasks = $runtime->getTasks();

        $this->assertTrue(is_array($tasks));
        $this->assertEquals(0, count($tasks));
    }

    public function testInvalidEnvironmentEmptyTasks()
    {
        $runtime = new Runtime();
        $runtime->setConfiguration(['environments' => ['invalid' => []]]);
        $runtime->setEnvironment('invalid');
        $runtime->setConfiguration(['environments' => []]);

        $tasks = $runtime->getTasks();

        $this->assertTrue(is_array($tasks));
        $this->assertEquals(0, count($tasks));
    }

    public function testInvalidEnvironments()
    {
        try {
            $runtime = new Runtime();
            $runtime->setEnvironment('invalid');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
        }

        try {
            $runtime = new Runtime();
            $runtime->setConfiguration(['environments' => ['valid' => []]]);
            $runtime->setEnvironment('invalid');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
        }
    }

    public function testTempFile()
    {
        $runtime = new Runtime();
        $tempFile = $runtime->getTempFile();

        $this->assertNotEquals('', $tempFile);
        $this->assertTrue(file_exists($tempFile));
        $this->assertTrue(is_readable($tempFile));
        $this->assertTrue(is_writable($tempFile));
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

    public function testSSHConfigPortDefinedInHostNotation()
    {
        $runtime = new Runtime();
        $runtime->setWorkingHost('223.12.24.64:1056');
        $sshConfig = $runtime->getSSHConfig();

        $this->assertEquals('1056', $sshConfig['port']);

        $runtime->setWorkingHost('223.12.24.64');
        $sshConfig = $runtime->getSSHConfig();

        $this->assertEquals('22', $sshConfig['port']);
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
        $timestamp = time();
        $this->assertTrue($process->isSuccessful());
        $this->assertEquals($timestamp, trim($process->getOutput()));

        /** @var Process $process */
        $process = $runtime->runLocalCommand('false');
        $this->assertFalse($process->isSuccessful());
    }

    public function testCurrentUser()
    {
        $runtime = new Runtime();
        $userData = posix_getpwuid(posix_geteuid());

        $this->assertTrue(is_array($userData));
        $this->assertArrayHasKey('name', $userData);
        $this->assertEquals($userData['name'], $runtime->getCurrentUser());
    }
}
