<?php
namespace Task;

use Mage\Task\AbstractTask;

class SampleTask extends AbstractTask
{
    public function getName()
    {
        return 'A Sample Task';
    }

    public function run()
    {
        return true;
    }
}
