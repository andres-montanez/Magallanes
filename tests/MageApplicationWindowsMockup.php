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

use Mage\Tests\Runtime\RuntimeWindowsMockup;
use Mage\MageApplication;
use Mage\Runtime\Runtime;

class MageApplicationWindowsMockup extends MageApplication
{
    /**
     * Gets the Runtime instance to use
     *
     * @return RuntimeWindowsMockup
     */
    protected function instantiateRuntime(): Runtime
    {
        return new RuntimeWindowsMockup();
    }
}
