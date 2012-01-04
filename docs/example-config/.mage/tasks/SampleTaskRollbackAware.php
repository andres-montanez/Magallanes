<?php
class Task_SampleTaskRollbackAware
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_RollbackAware
{
    public function getName()
    {
        if ($this->inRollback()) {
            return 'A Sample Task aware of rollbacks [in rollback]';
        } else {
            return 'A Sample Task aware of rollbacks [not in rollback]';
        }
    }

    public function run()
    {
        return true;
    }
}