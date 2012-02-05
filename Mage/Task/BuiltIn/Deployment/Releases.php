<?php
class Mage_Task_BuiltIn_Deployment_Releases
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        return 'Releasing [built-in]';
    }

    public function run()
    {
        if ($this->_config->release('enabled', false) == true) {
            $releasesDirectory = $this->_config->release('directory', 'releases');
            $symlink = $this->_config->release('symlink', 'current');

            $currentCopy = $releasesDirectory . '/' . $this->_config->getReleaseId();

            $userGroup = '';
            $resultFetch = $this->_runRemoteCommand('ls -ld ' . $currentCopy . ' | awk \'{print \$3\":\"\$4}\'', $userGroup);
            $command = 'rm -f ' . $symlink
                     . ' && '
                     . 'ln -sf ' . $currentCopy . ' ' . $symlink
                     . ' && '
                     . 'chown -h ' . $userGroup . ' ' . $symlink;
            $result = $this->_runRemoteCommand($command);
            return $result;

        } else {
            return false;
        }
    }

}