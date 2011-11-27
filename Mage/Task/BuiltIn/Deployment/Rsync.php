<?php
class Mage_Task_BuiltIn_Deployment_Rsync
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        if (isset($this->_config['deploy']['releases']['enabled'])) {
            if ($this->_config['deploy']['releases']['enabled'] == 'true') {
                return 'Rsync (with Releases) [built-in]';
            } else {
                return 'Rsync [built-in]';
            }
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
        if (isset($this->_config['deploy']['rsync-excludes'])) {
            $userExcludes = (array) $this->_config['deploy']['rsync-excludes'];
        } else {
            $userExcludes = array();
        }
        
        // If we are working with releases
        $deployToDirectory = $this->_config['deploy']['deploy-to'];
        if (isset($this->_config['deploy']['releases']['enabled'])) {
            if ($this->_config['deploy']['releases']['enabled'] == 'true') {
                if (isset($this->_config['deploy']['releases']['directory'])) {
                    $releasesDirectory = $this->_config['deploy']['releases']['directory'];
                } else {
                    $releasesDirectory = 'releases';
                }

                $deployToDirectory = rtrim($this->_config['deploy']['deploy-to'], '/')
                                   . '/' . $releasesDirectory
                                   . '/' . $this->_config['deploy']['releases']['_id'];
                $this->_runRemoteCommand('mkdir -p ' . $releasesDirectory . '/' . $this->_config['deploy']['releases']['_id']);
            }
        }

        $command = 'rsync -avz '
                 . $this->_excludes(array_merge($excludes, $userExcludes)) . ' '
                 . $this->_config['deploy']['deploy-from'] . ' '
                 . $this->_config['deploy']['user'] . '@' . $this->_config['deploy']['host'] . ':' . $deployToDirectory;

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