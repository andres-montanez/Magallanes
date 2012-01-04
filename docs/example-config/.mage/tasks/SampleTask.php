<?php
class Task_SampleTask
    extends Mage_Task_TaskAbstract
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