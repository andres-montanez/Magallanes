<?php

namespace Mage\Task\Newcraft\Resources;

use Mage\Task\AbstractTask;

/**
 * Class PreparePublicResourcesTask
 * @package Mage\Task\Newcraft\Filesystem
 */
class PreparePublicResourcesTask extends AbstractTask
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'preparing public resources for production [newcraft]';
    }

    /**
     * Runs NPN `build-prod` script that should in turn trigger all required preparation tasks
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        return $this->runCommandLocal('npm run -s build-prod');
    }
}
