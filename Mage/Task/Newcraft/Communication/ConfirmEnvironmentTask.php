<?php

namespace Mage\Task\Newcraft\Communication;

use Mage\Task\AbstractTask;
use Mage\Console;

class ConfirmEnvironmentTask extends AbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Confirm environment [newcraft]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        Console::output('');
        Console::output('Deploying to <white>'.$this->getConfig()->getEnvironment().'</white>! Sure? (yes/no)... ', 3, 0);
        $answer = strtolower(Console::readInput());
        Console::output('Cont... <purple>'.$this->getName().'</purple> ... ', 2, 0);
        return 'yes' === $answer;
    }
}