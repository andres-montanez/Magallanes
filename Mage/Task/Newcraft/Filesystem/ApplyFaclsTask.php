<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Applies Facls to defined directories with the provided flags
 * Class ApplyFaclsTask
 * @package Mage\Task\BuiltIn\Filesystem
 */
class ApplyFaclsTask extends AbstractTask implements IsReleaseAware
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Set file ACLs on remote system [newcraft]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     * @throws SkipException
     */
    public function run()
    {
        $releasesDirectory = $this->getConfig()->release('directory', 'releases');
        $currentCopy = $releasesDirectory . '/' . $this->getConfig()->getReleaseId();

        $flags = ' -'.$this->getParameter('flags', false).' ';

        $aclParam = $this->getParameter('acl_param', '');
        if (empty($aclParam)) {
            throw new SkipException('Parameter acl_param not set.');
        }

        $folders = $this->getParameter('folders', []);

        $return = true;
        foreach ($folders as $folder) {
            $execute = $this->runCommandRemote("setfacl$flags $aclParam $currentCopy/$folder", $output);
            if(!$execute) $return = false;
        }

        return $return;
    }
}