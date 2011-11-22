<?php
class Magallanes_Task_CSM_Git
{
    private $_url = null;
    
    public function run($url)
    {
        $this->_url = $url;
        $this->_update();
    }
    
    private function _update()
    {
        Magallanes_Console::output('git pull ' . $this->_url . PHP_EOL);
    }
}