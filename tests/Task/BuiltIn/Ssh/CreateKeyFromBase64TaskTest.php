<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Task\BuiltIn\Ssh;

use Mage\Task\BuiltIn\Ssh\CreateKeyFromBase64Task;
use PHPUnit_Framework_TestCase as TestCase;

class CreateKeyFromBase64TaskTest extends TestCase
{
    public function testCreateKeyFromBase64Task()
    {
        $tmpDir = '/tmp/mage_ssh_test';

        if (file_exists($tmpDir)) {
            unlink($tmpDir);
        }

        mkdir($tmpDir, 0777, true);
        putenv('HOME='.$tmpDir);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|\Mage\Runtime\Runtime $runtime
         */
        $runtime = $this->getMock('\Mage\Runtime\Runtime');
        $task = new CreateKeyFromBase64Task();

        $task->setOptions(['base64Key' => base64_encode('base64KeyValue')]);
        $task->setRuntime($runtime);
        $this->assertEquals('[SSH] Create ssh key from base64', $task->getDescription());

        self::assertTrue($task->execute());
        self::assertTrue(file_exists($tmpDir.'/.ssh/id_rsa'));
        self::assertEquals('base64KeyValue', file_get_contents($tmpDir.'/.ssh/id_rsa'));

        putenv('BASE64_KEY_ENV='.base64_encode('base64KeyFromEnvVarValue'));
        $task->setOptions(['base64KeyFromEnvVar' => 'BASE64_KEY_ENV', 'keyFileName' => 'custom_key']);

        self::assertTrue($task->execute());
        self::assertTrue(file_exists($tmpDir.'/.ssh/custom_key'));
        self::assertEquals('base64KeyFromEnvVarValue', file_get_contents($tmpDir.'/.ssh/custom_key'));
    }
}
