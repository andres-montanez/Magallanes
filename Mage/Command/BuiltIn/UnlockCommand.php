<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command\BuiltIn;

use Mage\Command\AbstractCommand;
use Mage\Command\RequiresEnvironment;
use Mage\Console;

/**
 * Command for Unlocking an Environment
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class UnlockCommand extends AbstractCommand implements RequiresEnvironment
{
    /**
     * Unlocks an Environment
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        $lockFile = getcwd() . '/.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }

        Console::output(
            'Unlocked deployment to <light_purple>'
            . $this->getConfig()->getEnvironment() . '</light_purple> environment',
            1,
            2
        );

        return 0;
    }
}
