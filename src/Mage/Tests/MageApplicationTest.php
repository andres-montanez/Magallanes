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

use Mage\MageApplication;
use Mage\Runtime\Exception\RuntimeException;
use Exception;
use PHPUnit_Framework_TestCase as TestCase;

class MageApplicationTest extends TestCase
{
    public function testValidConfiguration()
    {
        $application = new MageApplication();
        $application->configure(__DIR__ . '/Resources/basic.yml');
        $this->assertTrue($application instanceof MageApplication);
    }

    public function testInValidConfiguration()
    {
        try {
            $application = new MageApplication();
            $application->configure(__DIR__ . '/Resources/invalid.yml');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('The file "%s" does not have a valid Magallanes configuration.', __DIR__ . '/Resources/invalid.yml'), $exception->getMessage());
        }
    }

    public function testInValidFile()
    {
        try {
            $application = new MageApplication();
            $application->configure(__DIR__ . '/Resources/this-does-not-exists.yml');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('The file "%s" does not exists or is not readable.', __DIR__ . '/Resources/this-does-not-exists.yml'), $exception->getMessage());
        }
    }

}