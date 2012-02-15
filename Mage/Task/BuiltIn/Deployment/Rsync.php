<?php
class Mage_Task_BuiltIn_Deployment_Rsync
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        if ($this->_config->release('enabled', false) == true) {
            return 'Rsync (with Releases) [built-in]';
        } else {
                return 'Rsync [built-in]';
        }
    }

    public function run()
    {
        $excludes = array(
            '.git',
            '.svn',
            '.mage',
            '.gitignore'
        );
        
        // Look for User Excludes
        $userExcludes = $this->_config->deployment('excludes', array());
        
        // If we are working with releases
        $deployToDirectory = $this->_config->deployment('to');
        if ($this->_config->release('enabled', false) == true) {
            $releasesDirectory = $this->_config->release('directory', 'releases');

            $deployToDirectory = rtrim($this->_config->deployment('to'), '/')
                               . '/' . $releasesDirectory
                               . '/' . $this->_config->getReleaseId();
            $this->_runRemoteCommand('mkdir -p ' . $releasesDirectory . '/' . $this->_config->getReleaseId());
        }

        $command = 'rsync -avz '
                 . '--rsh="ssh -p' . $this->_config->deployment('port', '22') . '" '
                 . $this->_excludes(array_merge($excludes, $userExcludes)) . ' '
                 . $this->_config->deployment('from') . ' '
                 . $this->_config->deployment('user') . '@' . $this->_config->getHost() . ':' . $deployToDirectory;

        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
    
    private function _excludes(Array $excludes)
    {
        $excludesRsync = '';
        foreach ($excludes as $exclude) {
            $excludesRsync .= ' --exclude ' . $exclude . ' ';
        }
        
        $excludesRsync = trim($excludesRsync);
        return $excludesRsync;
    }
}