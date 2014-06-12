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
use Mage\Task\Factory;
use Mage\Console;

/**
 * This is an Alias of "release rollback"
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class RollbackCommand extends AbstractCommand implements RequiresEnvironment
{
    /**
     * Rollback a release
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        $releaseId = $this->getConfig()->getArgument(1);
        if (!is_numeric($releaseId)) {
            Console::output('<red>This release is mandatory.</red>', 1, 2);
            return false;
        }

        $lockFile = '.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile) && ($subcommand == 'rollback')) {
            Console::output('<red>This environment is locked!</red>', 1, 2);
            return null;
        }

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();

        if (count($hosts) == 0) {
            Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, unable to get releases.</dark_gray>', 1, 3);

        } else {
            foreach ($hosts as $host) {
                $this->getConfig()->setHost($host);

                $task = Factory::get('releases/rollback', $this->getConfig());
                $task->init();
                $task->setRelease($releaseId);
                $result = $task->run();
            }
        }

        return $result;
    }
}