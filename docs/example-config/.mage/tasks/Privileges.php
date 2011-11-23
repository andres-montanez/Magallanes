<?php
class Task_Privileges
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Fixing file privileges';
    }

    public function run()
    {
        $command = 'chown 33:33 . -R';
        $result = $this->_runRemoteCommand($command);
        
        return $result;
    }
}