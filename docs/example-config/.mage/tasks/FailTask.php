<?php
namespace Task;

use Mage\Task\AbstractTask;

class FailTask extends AbstractTask
{
    public function getName()
    {
        return 'A Failing Task';
    }

    public function run()
    {
        return false;
    }
}
