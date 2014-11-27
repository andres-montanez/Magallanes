<?php

namespace MageTest\Console;

use Mage\Console\Colors;
use PHPUnit_Framework_TestCase;

class ColorsTest extends PHPUnit_Framework_TestCase
{
    public function testColor()
    {
        $config = $this->getMock('Mage\Config');
        $config->expects($this->once())
            ->method('getParameter')
            ->will($this->returnValue(false));

        $string = '<green>FooBar</green>';

        // Method need to be non static in the future
        $result = Colors::color($string, $config);
        $expected = "\033[0;32mFooBar\033[0m";

        $this->assertSame($expected, $result);
    }

    public function testColorNoColor()
    {
        $config = $this->getMock('Mage\Config');
        $config->expects($this->once())
            ->method('getParameter')
            ->will($this->returnValue(true));

        $string = '<black>FooBar</black>';

        // Method need to be non static in the future
        $result = Colors::color($string, $config);
        $expected = 'FooBar';

        $this->assertSame($expected, $result);
    }
}
