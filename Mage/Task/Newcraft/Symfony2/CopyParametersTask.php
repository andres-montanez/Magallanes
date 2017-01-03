<?php

namespace Mage\Task\Newcraft\Symfony2;

use Mage\Task\BuiltIn\Symfony2\SymfonyAbstractTask;

/**
 * Task for Copying configuring use of environment specific parameters.yml file
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class CopyParametersTask extends SymfonyAbstractTask
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Symfony v2 - Prepare parameters.yml [newcraft]';
    }

    /**
     * Set correct parameters.yml file
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $envName = $this->getConfig()->deployment('environment');
        return $this->runCommandRemote('cp -fp app/config/parameters-'.$envName.'.yml app/config/parameters.yml');
    }

}
