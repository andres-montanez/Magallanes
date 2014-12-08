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
 * Task for Doctrine migrations
 */
class DoctrineMigrateTask extends SymfonyAbstractTask
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Symfony v2 - Migrate doctrine entities [built-in]';
    }

    /**
     * Migrates Doctrine entities
     *
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $env = $this->getParameter('env', 'dev');

        $command = $this->getAppPath() . ' doctrine:migrations:migrate -n --env=' . $env;
        
        return $this->runCommand($command);
    }
}
