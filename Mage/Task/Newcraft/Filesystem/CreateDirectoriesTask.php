<?php

namespace Mage\Task\Newcraft\Filesystem;

use Mage\Task\AbstractTask;

class CreateDirectoriesTask extends AbstractTask
{

    /**
     * @return string
     */
    public function getName()
    {
        $directoryNames = $this->getParameter('directories', []);
        return 'Creating '.count($directoryNames).' director'.(count($directoryNames) === 1 ? 'y' : 'ies').' [newcraft]';
    }

    /**
     * Removes any directory named current so it can be replaced with a symlink
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $directoryNames = $this->getParameter('directories', []);

        $return = true;
        foreach($directoryNames as $directoryName) {
            $result = $this->runCommandRemote('mkdir -p '.$directoryName);
            $return = $return && $result;
        }

        return $return;
    }
}
