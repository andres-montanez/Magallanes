<?php

namespace MageTest\Command\BuiltIn;

use Mage\Command\BuiltIn\UnlockCommand;
use MageTest\TestHelper\BaseTest;
use malkusch\phpmock\MockBuilder;

/**
 * Class UnlockCommandTest
 * @package MageTest\Command\BuiltIn
 * @coversDefaultClass Mage\Command\BuiltIn\UnlockCommand
 * @uses malkusch\phpmock\MockBuilder
 * @uses malkusch\phpmock\Mock
 * @uses Mage\Command\AbstractCommand
 * @uses Mage\Console
 * @uses Mage\Console\Colors
 */
class UnlockCommandTest extends BaseTest
{
    /**
     * @var UnlockCommand
     */
    private $unlockCommand;

    public static $isUnlinkCalled;
    public static $fileExistsResult;
    public static $isFileExists;

    /**
     * @before
     */
    public function before()
    {
        $this->unlockCommand = new UnlockCommand();

        self::$isUnlinkCalled = false;
        self::$fileExistsResult = false;
        self::$isFileExists = false;

        $mockBuilder = new MockBuilder();
        $fileExistsMock = $mockBuilder
            ->setName('file_exists')
            ->setNamespace('Mage\Command\BuiltIn')
            ->setFunction(
                function ($filePath) {
                    UnlockCommandTest::$fileExistsResult = $filePath;
                    return UnlockCommandTest::$isFileExists;
                }
            )
            ->build();
        $unlinkMock = $mockBuilder
            ->setName('unlink')
            ->setNamespace('Mage\Command\BuiltIn')
            ->setFunction(
                function () {
                    UnlockCommandTest::$isUnlinkCalled = true;
                }
            )
            ->build();
        $getCwdMock = $mockBuilder
            ->setNamespace('Mage\Command\BuiltIn')
            ->setName('getcwd')
            ->setFunction(
                function () {
                    return '';
                }
            )
            ->build();

        $fileExistsMock->disable();
        $unlinkMock->disable();
        $getCwdMock->disable();

        $fileExistsMock->enable();
        $unlinkMock->enable();
        $getCwdMock->enable();

        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('production');
        $this->unlockCommand->setConfig($configMock);

        $this->setUpConsoleStatics();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $expectedOutput = "\tUnlocked deployment to production environment\n\n";
        $this->expectOutputString($expectedOutput);
        $expectedLockFilePath = '/.mage/production.lock';

        self::$isFileExists = true;

        $actualExitCode = $this->unlockCommand->run();
        $expectedExitCode = 0;

        $this->assertTrue(self::$isUnlinkCalled);
        $this->assertEquals($expectedExitCode, $actualExitCode);
        $this->assertEquals($expectedLockFilePath, self::$fileExistsResult);
    }

    /**
     * @covers ::run
     */
    public function testRunIfFileNotExist()
    {
        $expectedOutput = "\tUnlocked deployment to production environment\n\n";
        $this->expectOutputString($expectedOutput);
        $expectedLockFilePath = '/.mage/production.lock';

        self::$isFileExists = false;

        $actualExitCode = $this->unlockCommand->run();
        $expectedExitCode = 0;

        $this->assertFalse(self::$isUnlinkCalled);
        $this->assertEquals($expectedExitCode, $actualExitCode);
        $this->assertEquals($expectedLockFilePath, self::$fileExistsResult);
    }
}
