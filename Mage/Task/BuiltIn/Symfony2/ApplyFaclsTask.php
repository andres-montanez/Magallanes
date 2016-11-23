<?php
namespace Mage\Task\BuiltIn\Symfony2;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;
use Mage\Task\Releases\IsReleaseAware;

/**
 * Task to setup Symfony file permission
 *
 * @see http://symfony.com/doc/current/setup/file_permissions.html Documentation of Symfony file permission setup.
 *
 * @author Fabio Del Bene <fabio.delbene@gmail.com>
 */
class ApplyFaclsTask extends AbstractTask implements IsReleaseAware
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Set Symfony file ACLs on remote system [built-in]';
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


        $httpUser = $this->getParameter('httpuser', '');
        if (empty($httpUser)) {
            throw new SkipException('Parameter httpuser not set.');
        }
        $localuser = $this->getParameter('localuser', '');
        if (empty($localuser)) {
            throw new SkipException('Parameter localuser not set.');
        }
        $folders = $this->getParameter('folders', []);

        foreach ($folders as $folder) {
            $folderPath = $currentCopy."/".$folder;
            $this->runCommandRemote($this->createFaclCommand('-R', $httpUser,$localuser, $folderPath), $output);
            $this->runCommandRemote($this->createFaclCommand('-dR', $httpUser,$localuser, $folderPath), $output);
        }

        return true;
    }

    public function createFaclCommand($setFaclOptions, $httpUser,$localuser, $folder)
    {
      return sprintf('setfacl %s -m u:%s:rwX -m u:%s:rwX %s',
        $setFaclOptions, $httpUser, $localuser, $folder
      );
    }

}
