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
use Mage\Task\BuiltIn\Releases\RollbackTask;
use Mage\Task\Factory;
use Mage\Console;

/**
 * Command for Managing the Releases
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ReleasesCommand extends AbstractCommand implements RequiresEnvironment
{
    /**
     * List the Releases, Rollback to a Release
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        $subcommand = $this->getConfig()->getArgument(1);
        $lockFile = '.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile) && ($subcommand == 'rollback')) {
            Console::output('<red>This environment is locked!</red>', 1, 2);
            return false;
        }

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();

        if (count($hosts) == 0) {
            Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, unable to get releases.</dark_gray>', 1, 3);

        } else {
            foreach ($hosts as $host) {
                $this->getConfig()->setHost($host);

                switch ($subcommand) {
                    case 'list':
                        $task = Factory::get('releases/list', $this->getConfig());
                        $task->init();
                        $result = $task->run();
                        break;

                    case 'rollback':
                        $releaseId = $this->getConfig()->getParameter('release', '');
                        /** @var RollbackTask $task */
                        $task = Factory::get('releases/rollback', $this->getConfig());
                        $task->init();
                        $task->setRelease($releaseId);
                        $result = $task->run();
                        break;
                }
            }
        }

        // TODO: $result is now always overwritten by the last host and has no default value

        return $result;
    }
}
