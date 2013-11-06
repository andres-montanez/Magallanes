<?php
namespace Task;

use Mage\Task\AbstractTask;

class TaskWithParameters extends AbstractTask
{
    public function getName()
    {
        $booleanOption = $this->getParameter('booleanOption', false);
        if ($booleanOption) {
            return 'A Sample Task With Parameters [booleanOption=true]';
        } else {
            return 'A Sample Task With Parameters [booleanOption=false]';
        }

    }

    public function run()
    {
        //throw new Mage_Task_SkipException;
        //return false;
        return true;
    }
}