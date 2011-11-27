<?php
class Mage_Task_BuiltIn_Deployment_Releases
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Releasing [built-in]';
    }

    public function run()
    {
        if (isset($this->_config['deploy']['releases']['enabled'])) {
            if ($this->_config['deploy']['releases']['enabled'] == 'true') {
                if (isset($this->_config['deploy']['releases']['directory'])) {
                    $releasesDirectory = $this->_config['deploy']['releases']['directory'];
                } else {
                    $releasesDirectory = 'releases';
                }
                if (isset($this->_config['deploy']['releases']['symlink'])) {
                    $symlink = $this->_config['deploy']['releases']['symlink'];
                } else {
                    $symlink = 'current';
                }

                $currentCopy = $releasesDirectory
                             . '/' . $this->_config['deploy']['releases']['_id'];

                $result = $this->_runRemoteCommand('ln -sf ' . $currentCopy . ' ' . $symlink);
                return $result;

            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}