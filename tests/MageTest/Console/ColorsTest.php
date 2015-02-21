<?php

namespace MageTest\Console;

use Mage\Console\Colors;
use PHPUnit_Framework_TestCase;

/**
 * @group Mage_Console_Colors
 * @coversDefaultClass Mage\Console\Colors
 */
class ColorsTest extends PHPUnit_Framework_TestCase
{
    private $noColorParameter = "no-color";
    /**
     * @group 159
     * @covers ::color
     */
    public function testColor()
    {
        $config = $this->getMock('Mage\Config');
        $config->expects($this->once())
            ->method('getParameter')
            ->with($this->noColorParameter)
            ->will($this->returnValue(false));

        $string = '<green>FooBar</green>';

        // Method need to be non static in the future
        $result = Colors::color($string, $config);
        $expected = "\033[0;32mFooBar\033[0m";

        $this->assertSame($expected, $result);
    }

    /**
     * @group 159
     * @covers ::color
     */
    public function testColorNoColor()
    {
        $config = $this->getMock('Mage\Config');
        $config->expects($this->once())
            ->method('getParameter')
            ->with($this->noColorParameter)
            ->will($this->returnValue(true));

        $string = '<black>FooBar</black>';

        // Method need to be non static in the future
        $result = Colors::color($string, $config);
        $expected = 'FooBar';

        $this->assertSame($expected, $result);
    }

    /**
     * @group 159
     * @covers ::color
     */
    public function testColorUnknownColorName()
    {
        $config = $this->getMock('Mage\Config');
        $config->expects($this->once())
            ->method('getParameter')
            ->with($this->noColorParameter)
            ->will($this->returnValue(false));

        $string = '<foo>FooBar</foo>';

        // Method need to be non static in the future
        $result = Colors::color($string, $config);

        $this->assertSame($string, $result);
    }
}
