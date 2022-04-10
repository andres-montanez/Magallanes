<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Tests\Runtime;

class RuntimeWindowsMockup extends RuntimeMockup
{
    public function isWindows(): bool
    {
        return true;
    }

    public function hasPosix(): bool
    {
        return false;
    }
}
