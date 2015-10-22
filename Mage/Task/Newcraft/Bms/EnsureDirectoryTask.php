<?php

namespace Mage\Task\Newcraft\Bms;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Console;

use Mage\Task\Newcraft\Filesystem\CreateDirectoriesTask;
use Mage\Task\Newcraft\Filesystem\ApplyFullAccessAclTask;


/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\Newcraft\Bms
 */
class EnsureDirectoryTask extends AbstractTask implements IsReleaseAware
{

    /**
     * @return string
     */
    public function getName()
    {
        $directoryName = $this->getParameter('name', '');
        return 'Ensure '.$directoryName.' directory is in place [newcraft]';
    }

    /**
     * Ensure there is a directory/symlink that contains the static content
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $directoryName = $this->getParameter('name', null);

        if(empty($directoryName)){
            return false;
        }

        //see if folder exists
        $this->runCommandRemote('test ! -d '.$directoryName.' && test ! -h '.$directoryName.' && echo \'n\' || echo \'y\'',$output);

        //create folder if it does not exist
        if('n' === $output){
            Console::output('<yellow>creating directory</yellow> ... ', 0, 0);
            $directoryParameters = [
                'directories' => [ '../../'.$directoryName ],
                'permissions' => $this->getParameter('permissions', false),
                'sudo' => $this->getParameter('sudo', false),
            ];
            $createDirectoryTask = new CreateDirectoriesTask($this->config,$this->inRollback(), $this->stage, $directoryParameters);
            $directoriesResult = $createDirectoryTask->run();
            if(false === $directoriesResult){
                return false;
            }
        }

        //create subdirectories
        $subdirectoryNames = array_map(function($v) use ($directoryName) { return '../../'.$directoryName.'/'.$v; },$this->getParameter('subdirectories', []));
        $subdirectoriesParameters = [
            'directories' => $subdirectoryNames,
            'permissions' => 775,
            'sudo' => $this->getParameter('sudo', false),
        ];

        $createSubdirectoriesTask = new CreateDirectoriesTask($this->config,$this->inRollback(), $this->stage, $subdirectoriesParameters);
        $subdirectoriesResult = $createSubdirectoriesTask->run();

        //set acls
        $aclParameters = [
          'directories' => [ '{project}/'.$directoryName ],
          'users' => $this->getParameter('users', []),
          'sudo' => $this->getParameter('sudo', false),
        ];

        $aclTask = new ApplyFullAccessAclTask($this->config,$this->inRollback(), $this->stage, $aclParameters);
        $aclResult = $aclTask->run();

        return $subdirectoriesResult && $aclResult;
    }
}
