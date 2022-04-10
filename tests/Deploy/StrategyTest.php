<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Deploy;

use Mage\Deploy\Strategy\ReleasesStrategy;
use Mage\Deploy\Strategy\RsyncStrategy;
use Mage\Runtime\Exception\RuntimeException;
use Mage\Runtime\Runtime;
use Exception;
use PHPUnit\Framework\TestCase;

class StrategyTest extends TestCase
{
    public function testCheckStateRsync()
    {
        $runtime = new Runtime();

        $rsync = new RsyncStrategy();
        $rsync->setRuntime($runtime);

        try {
            $runtime->setStage(Runtime::ON_DEPLOY);
            $rsync->getPreDeployTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::ON_DEPLOY, Runtime::PRE_DEPLOY), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $rsync->getOnDeployTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::ON_DEPLOY), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $rsync->getOnReleaseTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::ON_RELEASE), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $rsync->getPostReleaseTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::POST_RELEASE), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $rsync->getPostDeployTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::POST_DEPLOY), $exception->getMessage());
        }
    }

    public function testCheckStateReleases()
    {
        $runtime = new Runtime();

        $releases = new ReleasesStrategy();
        $releases->setRuntime($runtime);

        try {
            $runtime->setStage(Runtime::ON_DEPLOY);
            $releases->getPreDeployTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::ON_DEPLOY, Runtime::PRE_DEPLOY), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $releases->getOnDeployTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::ON_DEPLOY), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $releases->getOnReleaseTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::ON_RELEASE), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $releases->getPostReleaseTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::POST_RELEASE), $exception->getMessage());
        }

        try {
            $runtime->setStage(Runtime::PRE_DEPLOY);
            $releases->getPostDeployTasks();
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof RuntimeException);
            $this->assertEquals(sprintf('Invalid stage, got "%s" but expected "%s"', Runtime::PRE_DEPLOY, Runtime::POST_DEPLOY), $exception->getMessage());
        }
    }
}
