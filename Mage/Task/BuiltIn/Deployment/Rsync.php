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
        $ignores = array(
            '--exclude .git',
            '--exclude .svn',
            '--exclude .mage',
            '--exclude .gitignore'
        );

        $command = 'rsync -avz '
                 . implode(' ', $ignores) .' '
                 . $this->_config['deploy']['deploy-from'] . ' '
                 . $this->_config['deploy']['user'] . '@' . $this->_config['deploy']['host'] . ':' . $this->_config['deploy']['deploy-to'];

        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
}