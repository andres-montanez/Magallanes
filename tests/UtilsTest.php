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
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testStageNames()
    {
        $utils = new Utils();
        $this->assertEquals('Pre Deploy', $utils->getStageName(Runtime::PRE_DEPLOY));
        $this->assertEquals('On Deploy', $utils->getStageName(Runtime::ON_DEPLOY));
        $this->assertEquals('Post Deploy', $utils->getStageName(Runtime::POST_DEPLOY));
        $this->assertEquals('On Release', $utils->getStageName(Runtime::ON_RELEASE));
        $this->assertEquals('Post Release', $utils->getStageName(Runtime::POST_RELEASE));
        $this->assertEquals('invalid-stage', $utils->getStageName('invalid-stage'));
    }

    public function testReleaseDate()
    {
        $utils = new Utils();
        $releaseId = '20170102031530';
        $dateTime = $utils->getReleaseDate($releaseId);

        $this->assertTrue($dateTime instanceof DateTime);

        $this->assertEquals('2017-01-02 03:15:30', $dateTime->format('Y-m-d H:i:s'));
    }

    public function testTimeDiffs()
    {
        $utils = new Utils();
        $dateTime = new DateTime();
        $dateTime->modify('-1 second');
        $this->assertEquals('just now', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-45 seconds');
        $this->assertEquals('just now', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-90 seconds');
        $this->assertEquals('1 minute(s) ago', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-30 minutes');
        $this->assertEquals('30 minute(s) ago', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-1 hour');
        $this->assertEquals('1 hour(s) ago', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-10 hours');
        $this->assertEquals('10 hour(s) ago', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-1 day');
        $this->assertEquals('1 day(s) ago', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-3 days');
        $this->assertEquals('3 day(s) ago', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-7 days');
        $this->assertEquals('a week ago', $utils->getTimeDiff($dateTime));

        $dateTime = new DateTime();
        $dateTime->modify('-10 days');
        $this->assertEquals('', $utils->getTimeDiff($dateTime));
    }
}
