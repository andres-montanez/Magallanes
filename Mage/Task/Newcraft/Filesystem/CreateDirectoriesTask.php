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
     * Create directories
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $directoryNames = $this->getParameter('directories', []);
        $permissions = $this->getParameter('permissions', null);

        if(!is_numeric($permissions) && !preg_match('/^[ugoarwxX\,\-\+\= ]$/',$permissions)){
            $permissions = null;
        }

        $sudo = (bool) $this->getParameter('sudo', false) ? 'sudo ' : '';

        $return = true;
        foreach($directoryNames as $directoryName) {
            $result = $this->runCommandRemote('mkdir -p '.$directoryName);
            if(null !== $permissions){
                $result = $result && $this->runCommandRemote($sudo.'chmod '.$permissions.' '.$directoryName);
            }
            $return = $return && $result;
        }

        return $return;
    }
}
