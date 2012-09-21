<?php
class Mage_Command_BuiltIn_Update
    extends Mage_Command_CommandAbstract
{
    public function run()
    {
        $task = Mage_Task_Factory::get('scm/update', $this->getConfig());
        $task->init();

        Mage_Console::output('Updating application via ' . $task->getName() . ' ... ', 1, 0);
        $result = $task->run();

        if ($result == true) {
            Mage_Console::output('<green>OK</green>' . PHP_EOL, 0);
        } else {
            Mage_Console::output('<red>FAIL</red>' . PHP_EOL, 0);
        }
    }

}