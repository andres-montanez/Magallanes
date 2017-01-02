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
        if ($this->getConfig()->release('enabled')) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $currentCopy = $releasesDirectory.'/'.$this->getConfig()->getReleaseId();
        } else {
            $currentCopy = $this->getConfig()->deployment('to', '.');
        }

        $aclParam = $this->getParameter('acl_param', '');
        if (empty($aclParam)) {
            throw new SkipException('Parameter acl_param not set.');
        }

        $folders = $this->getParameter('folders', array());

        $flags = array();
        if ($this->getParameter('default', false)) {
            $flags[] = 'd';
        }
        if ($this->getParameter('recursive', false)) {
            $flags[] = 'R';
        }
        $flagStr = ($flags) ? ' -'.implode('', $flags).' ' : ' ';

        foreach ($folders as $folder) {
            $this->runCommandRemote("setfacl$flagStr-m $aclParam $currentCopy/$folder", $output);
        }

        return true;
    }
}
