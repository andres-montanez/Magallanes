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

use Mage\Tests\Runtime\RuntimeMockup;
use Mage\MageApplication;

class MageApplicationMockup extends MageApplication
{
    /**
     * Gets the Runtime instance to use
     *
     * @return RuntimeMockup
     */
    protected function instantiateRuntime(): RuntimeMockup
    {
        return new RuntimeMockup();
    }
}
