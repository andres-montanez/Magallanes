<?php

namespace Mage\Task\Newcraft\Filesystem;

use Mage\Task\AbstractTask;

class CreateSymlinkTask extends AbstractTask
{

    /**
     * @return string
     */
    public function getName()
    {
        $targetPath = $this->getParameter('target', null);
        $linkPath = $this->getParameter('link', null);
        return 'Creating symlink ('.$linkPath.' -> '.$targetPath.') [newcraft]';
    }

    /**
     * Removes any directory named current so it can be replaced with a symlink
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $targetPath = $this->getParameter('target', null);
        $linkPath = $this->getParameter('link', null);
        $sudo = (bool) $this->getParameter('sudo', false) ? 'sudo ' : '';

        if(null !== $targetPath && null !== $linkPath){
            $clearLinkPathCommand = 'test -h '.$linkPath.' && test -w '.$linkPath.' || '.$sudo.'rm -rf '.$linkPath.'; ';
            $createLinkCommand = 'ln -snf '.$targetPath.' '.$linkPath;
            $result = $this->runCommandRemote($clearLinkPathCommand.$createLinkCommand);
        }

        return isset($result) && $result;
    }
}
