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
        $exitCode = 100;
        $subCommand = $this->getConfig()->getArgument(1);

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();

        if (count($hosts) == 0) {
            Console::output(
                '<light_purple>Warning!</light_purple> <bold>No hosts defined, unable to get releases.</bold>',
                1, 3
            );

            return 101;
        }

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

            switch ($subCommand) {
                case 'list':
                    $task = Factory::get('releases/list', $this->getConfig());
                    $task->init();
                    $result = $task->run() && $result;
                    break;

                case 'rollback':
                    if (!is_numeric($this->getConfig()->getParameter('release', ''))) {
                        Console::output('<red>Missing required releaseid.</red>', 1, 2);

                        return 102;
                    }

                    $lockFile = getcwd() . '/.mage/' . $this->getConfig()->getEnvironment() . '.lock';
                    if (file_exists($lockFile)) {
                        Console::output('<red>This environment is locked!</red>', 1, 2);
                        echo file_get_contents($lockFile);

                        return 103;
                    }

                    $releaseId = $this->getConfig()->getParameter('release', '');
                    $this->getConfig()->setReleaseId($releaseId);
                    $task = Factory::get('releases/rollback', $this->getConfig());
                    $task->init();
                    $result = $task->run() && $result;
                    break;
            }
        }

        if ($result) {
            $exitCode = 0;
        }

        return $exitCode;
    }
}
