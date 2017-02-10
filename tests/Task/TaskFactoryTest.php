<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task;

use Mage\Task\TaskFactory;
use Mage\Runtime\Runtime;
use Mage\Runtime\Exception\RuntimeException;
use Exception;
use PHPUnit_Framework_TestCase as TestCase;

class TaskFactoryTest extends TestCase
{
    public function testClassNotExists()
    {
        try {
            $this->getFactory()->get('Foobar');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('The class "Foobar" does not exist', $exception->getMessage());
        }
    }

    public function testNotImplementingInterface()
    {
        try {
            $this->getFactory()->get('stdClass');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(
                'The class "stdClass" must implement the "Mage\Task\TaskInterface" interface',
                $exception->getMessage()
            );
        }
    }

    private function getFactory()
    {
        return new TaskFactory(new Runtime());
    }
}
