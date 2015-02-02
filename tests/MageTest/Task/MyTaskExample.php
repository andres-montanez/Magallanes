<?php

namespace My\Task;

use Mage\Task\AbstractTask;

class Example extends AbstractTask
{
    public function getName()
    {
        return 'I am an example task!';
    }

    public function run()
    {
        $command = 'echo "I do nothing"';
        return $this->runCommandLocal($command);
    }
}
