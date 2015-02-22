<?php

namespace MageTest\Command\BuiltIn;

use Mage\Command\BuiltIn\LockCommand;
use MageTest\TestHelper\BaseTest;
use malkusch\phpmock\FixedValueFunction;
use malkusch\phpmock\MockBuilder;

/**
 * Class LockCommandTest
 * @package MageTest\Command\BuiltIn
 * @coversDefaultClass Mage\Command\BuiltIn\LockCommand
 * @uses malkusch\phpmock\MockBuilder
 * @uses malkusch\phpmock\FixedValueFunction
 * @uses malkusch\phpmock\Mock
 * @uses Mage\Console\Colors
 * @uses Mage\Console
 * @uses Mage\Command\AbstractCommand
 */
class LockCommandTest extends BaseTest
{
    public static $fgetsCount;
    public static $mockName;
    public static $mockEmail;
    public static $mockDesc;
    public static $filePutContentsResult;
    public static $filePutContentsFile;

    /**
     * @var LockCommand
     */
    private $lockCommand;

    /**
     * @var FixedValueFunction
     */
    private $fgetsValue;

    /**
     * @before
     */
    public function before()
    {
        self::$fgetsCount = 0;
        self::$mockName = '';
        self::$mockEmail = '';
        self::$mockDesc = '';
        self::$filePutContentsResult = '';
        self::$filePutContentsFile = '';

        $this->lockCommand = new LockCommand();

        $mockBuilder = new MockBuilder();
        $fopenMock = $mockBuilder
            ->setName('fopen')
            ->setNamespace('Mage')
            ->setFunction(function () {
                return 'a';
            })
            ->build();

        $this->fgetsValue = new FixedValueFunction();
        $fgetsMock = $mockBuilder
            ->setNamespace('Mage')
            ->setName('fgets')
            ->setFunction(
                function () {
                    switch (LockCommandTest::$fgetsCount) {
                        case 0:
                            LockCommandTest::$fgetsCount++;
                            return LockCommandTest::$mockName;
                        case 1:
                            LockCommandTest::$fgetsCount++;
                            return LockCommandTest::$mockEmail;
                        case 2:
                            LockCommandTest::$fgetsCount++;
                            return LockCommandTest::$mockDesc;
                        default:
                            throw new \Exception('"fgets" count limit exceed');
                    }
                }
            )
            ->build();
        $getCwdMock = $mockBuilder
            ->setNamespace('Mage\Command\Builtin')
            ->setName('getcwd')
            ->setFunction(
                function () {
                    return '';
                }
            )
            ->build();
        $fileGetContentsMock = $mockBuilder
            ->setNamespace('Mage\Command\Builtin')
            ->setName('file_put_contents')
            ->setFunction(
                function ($file, $contents) {
                    LockCommandTest::$filePutContentsFile = $file;
                    LockCommandTest::$filePutContentsResult = $contents;
                }
            )
            ->build();

        $dateMock = $mockBuilder
            ->setNamespace('Mage\Command\BuiltIn')
            ->setName('date')
            ->setFunction(
                function () {
                    return '2015-01-01 12:00:00';
                }
            )
            ->build();

        $fopenMock->disable();
        $fgetsMock->disable();
        $getCwdMock->disable();
        $fileGetContentsMock->disable();
        $dateMock->disable();

        $fopenMock->enable();
        $fgetsMock->enable();
        $getCwdMock->enable();
        $fileGetContentsMock->enable();
        $dateMock->enable();

        $this->setUpConsoleStatics();
    }

    public function lockCommandProvider()
    {
        return array(
            'normal' => array(
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'description' => "There's a critical bug here!",
                'expectedLockFileContents' => "Locked environment at date: 2015-01-01 12:00:00\n"
                    . "Locked by John Smith (john.smith@example.com)\n"
                    . "There's a critical bug here!\n",
            ),
            'with_no_name' => array(
                'name' => '',
                'email' => 'john.smith@example.com',
                'description' => "There's a critical bug here!",
                'expectedLockFileContents' => "Locked environment at date: 2015-01-01 12:00:00\n"
                    . "(john.smith@example.com)\n"
                    . "There's a critical bug here!\n",
            ),
            'with_no_email' => array(
                'name' => 'John Smith',
                'email' => '',
                'description' => "There's a critical bug here!",
                'expectedLockFileContents' => "Locked environment at date: 2015-01-01 12:00:00\n"
                    . "Locked by John Smith \n"
                    . "There's a critical bug here!\n",
            ),
            'with_no_name_nor_email' => array(
                'name' => '',
                'email' => '',
                'description' => "There's a critical bug here!",
                'expectedLockFileContents' => "Locked environment at date: 2015-01-01 12:00:00\n"
                    . "\n"
                    . "There's a critical bug here!\n",
            ),
            'with_no_desciption' => array(
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'description' => '',
                'expectedLockFileContents' => "Locked environment at date: 2015-01-01 12:00:00\n"
                    . "Locked by John Smith (john.smith@example.com)"
            ),
        );
    }

    /**
     * @covers ::run
     * @dataProvider lockCommandProvider
     */
    public function testRun($name, $email, $description, $expectedLockFileContents)
    {
        $expectedOutput = "Your name (enter to leave blank): "
            . "Your email (enter to leave blank): "
            . "Reason of lock (enter to leave blank): "
            . "\tLocked deployment to production environment\n\n";
        $this->expectOutputString($expectedOutput);
        $expectedLockFilePath = '/.mage/production.lock';
        $expectedExitCode = 0;

        self::$mockName = $name;
        self::$mockEmail = $email;
        self::$mockDesc = $description;

        $configMock = $this->getMock('Mage\Config');
        $configMock->expects($this->atLeastOnce())
            ->method('getEnvironment')
            ->willReturn('production');
        $this->lockCommand->setConfig($configMock);

        $actualExitCode = $this->lockCommand->run();

        $this->assertEquals($expectedExitCode, $actualExitCode);
        $this->assertEquals($expectedLockFileContents, self::$filePutContentsResult);
        $this->assertEquals($expectedLockFilePath, self::$filePutContentsFile);
    }
}
