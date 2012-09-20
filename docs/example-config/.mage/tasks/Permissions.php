<?php
class Task_Permissions
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Fixing file permissions';
    }

    public function run()
    {
        $command = 'chmod 755 . -R';
        $result = $this->_runRemoteCommand($command);

        return $result;
    }
}
