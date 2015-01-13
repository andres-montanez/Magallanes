<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;
use Mage\Task\Releases\IsReleaseAware;

class ApplyFaclsTask extends AbstractTask implements IsReleaseAware
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Set file ACLs on remote system [built-in]';
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


        $aclParam = $this->getParameter('acl_param', '');
        if (empty($aclParam)) {
            throw new SkipException('Parameter acl_param not set.');
        }

        $folders = $this->getParameter('folders', []);
        $recursive = $this->getParameter('recursive', false) ? ' -R ' : ' ';

        foreach ($folders as $folder) {
            $this->runCommandRemote("setfacl$recursive-m $aclParam $currentCopy/$folder", $output);
        }

        return true;
    }
}
