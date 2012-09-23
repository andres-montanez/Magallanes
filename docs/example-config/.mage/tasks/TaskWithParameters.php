<?php
class Task_TaskWithParameters
    extends Mage_Task_TaskAbstract
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
        return true;
    }
}