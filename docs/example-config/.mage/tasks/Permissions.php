<?php
namespace Task;

use Mage\Task\AbstractTask;

class Permissions extends AbstractTask
{
    public function getName()
    {
        return 'Fixing file permissions';
    }

    public function run()
    {
        $command = 'chmod 755 . -R';
        $result = $this->runCommandRemote($command);

        return $result;
    }
}
