<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests;

use Mage\Utils;
use Mage\Runtime\Runtime;
use DateTime;
use PHPUnit_Framework_TestCase as TestCase;

class UtilsTest extends TestCase
{
    public function testStageNames()
    {
        $this->assertEquals('Pre Deployment', Utils::getStageName(Runtime::PRE_DEPLOY));
        $this->assertEquals('On Deployment', Utils::getStageName(Runtime::ON_DEPLOY));
        $this->assertEquals('Post Deployment', Utils::getStageName(Runtime::POST_DEPLOY));
        $this->assertEquals('On Release', Utils::getStageName(Runtime::ON_RELEASE));
        $this->assertEquals('Post Release', Utils::getStageName(Runtime::POST_RELEASE));
    }

    public function testReleaseDate()
    {
        $releaseId = '20170102031530';
        $dateTime = Utils::getReleaseDate($releaseId);

        $this->assertTrue($dateTime instanceof DateTime);

        $this->assertEquals('2017-01-02 03:15:30', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testTimeDiffs()
    {
        $dateTime = new DateTime();
        $dateTime->modify('-1 second');
        $this->assertEquals('just now', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-45 seconds');
        $this->assertEquals('45 seconds ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-90 seconds');
        $this->assertEquals('one minute ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-30 minutes');
        $this->assertEquals('30 minutes ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-1 hour');
        $this->assertEquals('one hour ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-10 hours');
        $this->assertEquals('10 hours ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-1 day');
        $this->assertEquals('one day ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-3 days');
        $this->assertEquals('3 days ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-7 days');
        $this->assertEquals('a week ago', Utils::getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-10 days');
        $this->assertEquals('', Utils::getTimeDiff($dateTime));
    }
}
