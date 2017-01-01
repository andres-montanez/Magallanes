<?php
namespace Mage\Tests;

use Mage\Utils;
use Mage\Runtime\Runtime;
use DateTime;
use PHPUnit_Framework_TestCase as TestCase;

class UtilsTest extends TestCase
{
    public function testStageNames()
    {
        $this->assertEquals(Utils::getStageName(Runtime::PRE_DEPLOY), 'Pre Deployment');
        $this->assertEquals(Utils::getStageName(Runtime::ON_DEPLOY), 'On Deployment');
        $this->assertEquals(Utils::getStageName(Runtime::POST_DEPLOY), 'Post Deployment');
        $this->assertEquals(Utils::getStageName(Runtime::ON_RELEASE), 'On Release');
        $this->assertEquals(Utils::getStageName(Runtime::POST_RELEASE), 'Post Release');
    }

    public function testReleaseDate()
    {
        $releaseId = '20170102031530';
        $dateTime = Utils::getReleaseDate($releaseId);

        $this->assertTrue($dateTime instanceof DateTime);

        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), '2017-01-02 03:15:30');
    }

    public function testTimeDiffs()
    {
        $dateTime = new DateTime();
        $dateTime->modify('-1 second');
        $this->assertEquals(Utils::getTimeDiff($dateTime), 'just now');

        $dateTime = new DateTime();
        $dateTime->modify('-45 seconds');
        $this->assertEquals(Utils::getTimeDiff($dateTime), '45 seconds ago');

        $dateTime = new DateTime();
        $dateTime->modify('-90 seconds');
        $this->assertEquals(Utils::getTimeDiff($dateTime), 'one minute ago');

        $dateTime = new DateTime();
        $dateTime->modify('-30 minutes');
        $this->assertEquals(Utils::getTimeDiff($dateTime), '30 minutes ago');

        $dateTime = new DateTime();
        $dateTime->modify('-1 hour');
        $this->assertEquals(Utils::getTimeDiff($dateTime), 'one hour ago');

        $dateTime = new DateTime();
        $dateTime->modify('-10 hours');
        $this->assertEquals(Utils::getTimeDiff($dateTime), '10 hours ago');

        $dateTime = new DateTime();
        $dateTime->modify('-1 day');
        $this->assertEquals(Utils::getTimeDiff($dateTime), 'one day ago');

        $dateTime = new DateTime();
        $dateTime->modify('-3 days');
        $this->assertEquals(Utils::getTimeDiff($dateTime), '3 days ago');

        $dateTime = new DateTime();
        $dateTime->modify('-7 days');
        $this->assertEquals(Utils::getTimeDiff($dateTime), 'a week ago');

        $dateTime = new DateTime();
        $dateTime->modify('-10 days');
        $this->assertEquals(Utils::getTimeDiff($dateTime), '');
    }
}
