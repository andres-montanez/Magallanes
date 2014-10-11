<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Symfony2;

use Mage\Task\BuiltIn\Symfony2\SymfonyAbstractTask;

/**
 * Task for Clearing the Cache
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class CacheClearTask extends SymfonyAbstractTask
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Symfony v2 - Cache Clear [built-in]';
    }

    /**
     * Clears the Cache
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        // Options
        $env = $this->getParameter('env', 'dev');

        $command = $this->getAppPath() . ' cache:clear --env=' . $env;
        $result = $this->runCommand($command);

        return $result;
    }
}
