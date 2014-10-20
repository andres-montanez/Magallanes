<?php
namespace Task;

use Mage\Task\AbstractTask;

class Privileges extends AbstractTask
{
    public function getName()
    {
        return 'Fixing file privileges';
    }

    public function run()
    {
        $command = 'chown 33:33 . -R';
        $result = $this->runCommandRemote($command);

        return $result;
    }
}
