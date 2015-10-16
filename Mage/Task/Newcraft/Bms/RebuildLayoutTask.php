<?php

namespace Mage\Task\Newcraft\Bms;

use Mage\Task\BuiltIn\Symfony2\SymfonyAbstractTask;

class RebuildLayoutTask extends SymfonyAbstractTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'BMS - Rebuild Layout [newcraft]';
    }

    /**
     * Installs Assets
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $env = $this->getParameter('env', 'dev');
        return $this->runCommandRemote($this->getAppPath() . ' bms:genLayout --env='.$env);
    }
}
