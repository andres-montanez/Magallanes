<?php
class Mage_Task_BuiltIn_Deployment_Rsync
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Rsync (built-in)';
    }

    public function run($config)
    {
        $ignores = array(
            '--exclude .git',
            '--exclude .svn',
            '--exclude .mage',
            '--exclude .gitignore'
        );

        $command = 'rsync -avz '
                 . implode(' ', $ignores) .' '
                 . $config['deploy']['deploy-from'] . ' '
                 . $config['deploy']['user'] . '@' . $config['deploy']['host'] . ':' . $config['deploy']['deploy-to'];

        $result = $this->_runLocalCommand($command);
        
        return $result;
    }
}