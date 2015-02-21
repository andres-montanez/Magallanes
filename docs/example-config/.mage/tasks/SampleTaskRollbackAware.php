<?php
namespace Task;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\RollbackAware;

class SampleTaskRollbackAware extends AbstractTask implements RollbackAware
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
