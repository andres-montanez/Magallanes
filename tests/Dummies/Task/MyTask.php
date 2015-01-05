<?php

namespace Task;

use Exception;
use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;
use Mage\Task\SkipException;

class MyTask extends AbstractTask
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
     * @throws Exception
     * @throws ErrorWithMessageException
     * @throws SkipException
     */
    public function run()
    {
        return true;
    }
}
 