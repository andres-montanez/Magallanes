<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Stepan Yamilov <yamilovs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Deploy\Strategy;

use Mage\Runtime\Exception\RuntimeException;
use Mage\Runtime\Runtime;

abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * @var Runtime
     */
    protected $runtime;

    /**
     * @param Runtime $runtime
     */
    public function setRuntime(Runtime $runtime)
    {
        $this->runtime = $runtime;
    }

    /**
     * Check the runtime stage is correct
     * @param $stage
     * @throws RuntimeException
     */
    protected function checkStage($stage)
    {
        if ($this->runtime->getStage() !== $stage) {
            throw new RuntimeException(sprintf('Invalid stage, got "%s" but expected "%"', $this->runtime->getStage(), $stage));
        }
    }
}