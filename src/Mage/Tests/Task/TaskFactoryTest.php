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
    public function testNonInstantiable()
    {
        $runtime = new Runtime();
        $factory = new TaskFactory($runtime);

        try {
            $factory->get('Traversable');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('Invalid task name "Traversable"', $exception->getMessage());
        }
    }

    public function testNotExtendingAbstractTask()
    {
        $runtime = new Runtime();
        $factory = new TaskFactory($runtime);

        try {
            $factory->get('stdClass');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals('Invalid task name "stdClass"', $exception->getMessage());
        }
    }
}
