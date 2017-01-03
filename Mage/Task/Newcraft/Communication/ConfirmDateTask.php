<?php

namespace Mage\Task\Newcraft\Communication;

use Mage\Task\AbstractTask;
use Mage\Console;

class ConfirmDateTask extends AbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Confirm date & time [newcraft]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     */
    public function run()
    {
        $now = new \DateTime(null, new \DateTimeZone('Europe/Amsterdam'));
        $isLate = 14 < (int)$now->format('G');
        $isWeekend = 4 < (int)$now->format('N');

        if ($isLate || $isWeekend) {
            Console::output(''); //newline for nice output.
        } else {
            return true;
        }

        if($isLate) {
            Console::output('It\'s after 15:00u. Sure? (yes/no)... ', 3, 0);
            if('yes' !== strtolower(Console::readInput())) {
                return false;
            }
        }

        if ($isWeekend) {
            Console::output(
                'It\'s '. ($isLate ? 'also' : '') . ' ' . $now->format('l').
                '. ' . ($isLate ? 'Really, REALLY sure?' : 'Sure? ') . ' (yes/no)... ', 3, 0);
            if('yes' !== strtolower(Console::readInput())) {
                return false;
            }
        }

        Console::output('Cont... <purple>'.$this->getName().'</purple> ... ', 2, 0);
        return true;
    }
}