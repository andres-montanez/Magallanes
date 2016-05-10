<?php

namespace MageTest;

use Mage\Mailer;
use MageTest\TestHelper\BaseTest;
use malkusch\phpmock\MockBuilder;

/**
 * Class MailerTest
 * @package MageTest\Command\BuiltIn
 * @coversDefaultClass Mage\Mailer
 * @uses Mage\Console
 * @uses malkusch\phpmock\MockBuilder
 */
class MailerTest extends BaseTest
{
    /**
     * @covers ::send
     */
    public function testRegularSend()
    {

        $mailExecuted = false;

        $builder = new MockBuilder();
        $mockMail = $builder
            ->setNamespace('Mage')
            ->setName('mail')
            ->setFunction(
                function () use ($mailExecuted){
                    return true;
                }
            )
            ->build();

        $mockFileGetContents = $builder
            ->setNamespace('Mage')
            ->setName('file_get_contents')
            ->setFunction(
                function () {
                    return true;
                }
            )
            ->build();

        $mockMail->enable();
        $mockFileGetContents->enable();

        $mailer = new Mailer();
        $mailer->setLogFile('test');
        $result = $mailer->send(true);

        $this->assertTrue($result);

        $mockMail->disable();
        $mockFileGetContents->disable();

    }
}
