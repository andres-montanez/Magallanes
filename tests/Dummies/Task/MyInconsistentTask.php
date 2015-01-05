<?php

namespace Task;

class MyInconsistentTask
{
    /**
     * Returns the Title of the Task
     *
     * @return string
     */
    public function getName()
    {
        return 'my task';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        return true;
    }
}
 