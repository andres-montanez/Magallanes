<?php

namespace Mage\Task\Newcraft\Filesystem;

use Mage\Task\AbstractTask;

/**
 * Applies full access Facls (read, write & execute on directories) to defined directories for defined users
 * Class ApplyFullAccessAclTask
 * @package Mage\Task\Newcraft\Filesystem
 */
class ApplyFullAccessAclTask extends AbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        $userNames = $this->getParameter('users', []);
        $directoryNames = $this->getParameter('directories', []);
        return 'Set full access ACLs for '.count($userNames).' user'.(count($userNames) === 1 ? '' : 's').' on '.count($directoryNames).' director'.(count($directoryNames) === 1 ? 'y' : 'ies').' [newcraft]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {

        $userNames = $this->getParameter('users', []);
        $usersCommandString = implode(' -m ',array_map(function($v){ return 'u:'.$v.':rwX'; }, $userNames));

        $directoryNames = $this->getParameter('directories', []);

        $sudo = (bool) $this->getParameter('sudo', false) ? 'sudo ' : '';

        $projectDirectory = rtrim($this->getConfig()->deployment('to'), '/');
        $releaseDirectory = $projectDirectory . '/' . $this->getConfig()->release('directory', 'releases') . '/' . $this->getConfig()->getReleaseId();

        $return = true;
        foreach($directoryNames as $directoryName) {

            if (0 === strpos($directoryName, '{release}')) {
                $directoryPath = $releaseDirectory . '/' . ltrim(str_replace('{release}', '', $directoryName), '/');
            } elseif (0 === strpos($directoryName, '{project}')) {
                $directoryPath = $projectDirectory . '/' . ltrim(str_replace('{project}', '', $directoryName), '/');
            } else {
                $directoryPath = $directoryName;
            }

            $fileResult = $this->runCommandRemote($sudo.'setfacl -Rnm '.$usersCommandString.' '.$directoryPath, $fileOutput);
            $directoryResult = $this->runCommandRemote($sudo.'setfacl -dRnm '.$usersCommandString.' '.$directoryPath, $directoryOutput);
            $return = $return && $fileResult && $directoryResult;
        }

        return $return;
    }
}