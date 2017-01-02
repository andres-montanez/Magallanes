<?php

namespace MageTest\Command;

use Mage\Config;
use PHPUnit_Framework_TestCase;

/**
 * @group Mage_Config
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testOverrideDeploymentOptionWithEnvironemntVariable()
    {
        $config = new Config();

        $expected = 'b';
        $actual = $config->deployment('a', 'b');

        $this->assertSame($expected, $actual);

        putenv('MAGE_DEPLOYMENT_A=c');

        $expected = 'c';
        $actual = $config->deployment('a', 'b');

        $this->assertSame($expected, $actual);
    }

    public function testOverrideReleaseOptionWithEnvironemntVariable()
    {
        $config = new Config();

        $expected = 'b';
        $actual = $config->release('a', 'b');

        $this->assertSame($expected, $actual);

        putenv('MAGE_RELEASE_A=c');

        $expected = 'c';
        $actual = $config->release('a', 'b');

        $this->assertSame($expected, $actual);
    }
}
