<?php

namespace MageTest\Command\BuiltIn;

use Mage\Command\BuiltIn\DeployCommand;
use MageTest\TestHelper\BaseTest;

/**
 * Class DeployCommandTest
 * @package MageTest\Command\BuiltIn
 * @coversDefaultClass Mage\Command\BuiltIn\DeployCommand
 */
class DeployCommandTest extends BaseTest
{
    /**
     * @covers ::__construct
     * @covers ::sendNotification
     */
    public function testSendRegularNotification()
    {
        $mailerMock = $this->getMock('Mage\Mailer');
        $mailerMock
            ->method('setAddress')
            ->willReturnSelf();

        $mailerMock
            ->method('setProject')
            ->willReturnSelf();

        $mailerMock
            ->method('setLogFile')
            ->willReturnSelf();

        $mailerMock
            ->method('setEnvironment')
            ->willReturnSelf();

        $mailerMock
            ->expects($this->once())
            ->method('send');

        $configMock = $this->getMock('Mage\Config');
        $configMock
            ->method('general')
            ->willReturn(true);

        $deployCommand = new DeployCommand();
        $deployCommand->setConfig($configMock);

        $this->callMethod(
            $deployCommand,
            'sendNotification',
            array(true, $mailerMock));
    }

    public function testIgnoreNotification()
    {
        $mailerMock = $this->getMock('Mage\Mailer');

        $mailerMock
            ->expects($this->never())
            ->method('send');

        $configMock = $this->getMock('Mage\Config');
        $configMock
            ->method('general')
            ->willReturn(false);

        $deployCommand = new DeployCommand();
        $deployCommand->setConfig($configMock);
        $this->callMethod($deployCommand, 'sendNotification', array(true, $mailerMock));
    }

    /**
     * @covers ::__construct
     * @covers ::sendNotification
     */
    public function testSendNotificationWithEmailOptions()
    {
        $mailerMock = $this->getMock('Mage\Mailer');
        $mailerMock
            ->method('setAddress')
            ->willReturnSelf();

        $mailerMock
            ->method('setProject')
            ->willReturnSelf();

        $mailerMock
            ->method('setLogFile')
            ->willReturnSelf();

        $mailerMock
            ->method('setEnvironment')
            ->willReturnSelf();

        $mailerMock
            ->expects($this->once())
            ->method('setCc')
            ->willReturnSelf();

        $mailerMock
            ->expects($this->once())
            ->method('setBcc')
            ->willReturnSelf();

        $mailerMock
            ->expects($this->once())
            ->method('send');

        $configMock = $this->getMock('Mage\Config');

        $configMock
            ->method('general')
            ->will($this->returnCallback(function($option, $default) {
                if (strcmp($option, 'email_options') === 0) {
                    return array('bcc' => true, 'cc' => true);
                }
                return true;
            }));

        $deployCommand = new DeployCommand();
        $deployCommand->setConfig($configMock);

        $this->callMethod(
            $deployCommand,
            'sendNotification',
            array(true, $mailerMock));
    }
}
