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
        $exitCode = 105;
        $releaseId = $this->getConfig()->getArgument(1);

        if (!is_numeric($releaseId)) {
            Console::output('<red>This release is mandatory.</red>', 1, 2);
            return 104;
        }

        $lockFile = getcwd() . '/.mage/' . $this->getConfig()->getEnvironment() . '.lock';
        if (file_exists($lockFile)) {
            Console::output('<red>This environment is locked!</red>', 1, 2);
            echo file_get_contents($lockFile);
            return 106;
        }

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();

        if (count($hosts) == 0) {
            Console::output('<light_purple>Warning!</light_purple> <bold>No hosts defined, unable to get releases.</bold>', 1, 3);
        } else {
            $result = true;
            foreach ($hosts as $hostKey => $host) {
                // Check if Host has specific configuration
                $hostConfig = null;
                if (is_array($host)) {
                    $hostConfig = $host;
                    $host = $hostKey;
                }

                // Set Host and Host Specific Config
                $this->getConfig()->setHost($host);
                $this->getConfig()->setHostConfig($hostConfig);

                $this->getConfig()->setReleaseId($releaseId);
                $task = Factory::get('releases/rollback', $this->getConfig());
                $task->init();
                $result = $task->run() && $result;
            }

            if ($result) {
                $exitCode = 0;
            }
        }

        return $exitCode;
    }
}
