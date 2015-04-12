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
 * Command for Locking the Deployment to an Environment
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class LockCommand extends AbstractCommand implements RequiresEnvironment
{
    /**
     * Locks the Deployment to a Environment
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        Console::output('Your name (enter to leave blank): ', 0, 0);
        $name = Console::readInput();
        Console::output('Your email (enter to leave blank): ', 0, 0);
        $email = Console::readInput();
        Console::output('Reason of lock (enter to leave blank): ', 0, 0);
        $reason = Console::readInput();

        $lockmsg = PHP_EOL;
        if ($name) {
            $lockmsg .= 'Locked by ' . $name . ' ';
        }
        if ($email) {
            $lockmsg .= '(' . $email . ')';
        }
        if ($reason) {
            $lockmsg .= PHP_EOL . $reason . PHP_EOL;
        }

        $lockFile = getcwd() . '/.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        file_put_contents($lockFile, 'Locked environment at date: ' . date('Y-m-d H:i:s') . $lockmsg);

        Console::output(
            'Locked deployment to <light_purple>'
            . $this->getConfig()->getEnvironment()
            . '</light_purple> environment',
            1,
            2
        );

        return 0;
    }
}
