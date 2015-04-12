<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\General;

use Mage\Task\AbstractTask;

/**
 * Task for running multiple custom commands setting them manually
 *
 * Example of usage:
 *
 * tasks:
 *    on-deploy:
 *       - scm/force-update
 *       - general/manually:
 *          - find . -type d -exec chmod 755 {} \;
 *          - find . -type f -exec chmod 644 {} \;
 *          - chmod +x bin/console
 *          - find var/logs -maxdepth 1 -type f -name '*.log' -exec chown apache:apache {} \;
 *       - symfony2/cache-clear
 *
 * @author Samuel Chiriluta <samuel4x4@gmail.com>
 */
class ManuallyTask extends AbstractTask
{

    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Manually multiple custom tasks';
    }

    /**
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $result = true;
        
        $commands = $this->getParameters();

        foreach ($commands as $command) {
            $result = $result && $this->runCommand($command);
        }

        return $result;
    }
}
