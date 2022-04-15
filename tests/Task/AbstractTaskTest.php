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

use Mage\Task\Exception\ErrorException;
use Exception;
use PHPUnit\Framework\TestCase;

class AbstractTaskTest extends TestCase
{
    public function testFailingTask()
    {
        $task = new TestCaseFailTask();

        try {
            $task->execute();
            $this->assertTrue(false, 'TestCaseFailTask did not throw exception');
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof ErrorException);

            if ($exception instanceof ErrorException) {
                $this->assertEquals('This is a text...', $exception->getTrimmedMessage(14));
                $this->assertEquals('This is a text with a lot of characters', $exception->getTrimmedMessage());
                $this->assertEquals('This is a text with a lot of characters', $exception->getMessage());
            }
        }
    }
}
