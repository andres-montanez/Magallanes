<?php
class Mage_Task_BuiltIn_Scm_Update
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'SCM Update (built-in)';
    }
    
    public function run($config)
    {
        switch ($config['scm']['type']) {
            case 'git':
                $command = 'git pull';
                break;

            case 'svn':
                $command = 'svn update';
                break;
        }

        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
}