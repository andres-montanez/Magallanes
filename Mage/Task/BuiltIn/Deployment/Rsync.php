<?php
class Mage_Task_BuiltIn_Deployment_Rsync
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Rsync [built-in]';
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

        $command = 'rsync -avz '
                 . $this->_excludes(array_merge($excludes, $userExcludes)) . ' '
                 . $this->_config['deploy']['deploy-from'] . ' '
                 . $this->_config['deploy']['user'] . '@' . $this->_config['deploy']['host'] . ':' . $this->_config['deploy']['deploy-to'];

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