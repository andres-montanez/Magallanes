<?php
class Mage_Task_BuiltIn_Deployment_Rsync
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        if ($this->_config->release('enabled', false) == true) {
            if ($this->getActionOption('overrideRelease', false) == true) {
                return 'Rsync (with Releases override) [built-in]';
            } else {
                return 'Rsync (with Releases) [built-in]';                
            }
        } else {
                return 'Rsync [built-in]';
        }
    }

    public function run()
    {
        $overrideRelease = $this->getActionOption('overrideRelease', false);
        
        if ($overrideRelease == true) {
            $releaseToOverride = false;
            $resultFetch = $this->_runRemoteCommand('ls -ld current | cut -d\"/\" -f2', $releaseToOverride);
            if (is_numeric($releaseToOverride)) {
                $this->_config->setReleaseId($releaseToOverride);
            }
        }

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
                 . '--rsh="ssh -p' . $this->_config->getHostPort() . '" '
                 . $this->_excludes(array_merge($excludes, $userExcludes)) . ' '
                 . $this->_config->deployment('from') . ' '
                 . $this->_config->deployment('user') . '@' . $this->_config->getHostName() . ':' . $deployToDirectory;

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