<?php
class Magallanes_Task_Deploy
{
    private $_config = null;
    
    public function run(Magallanes_Config $config)
    {
        $this->_config = $config;
        $this->_rsync();
    }
    
    private function _rsync()
    {
        $config = $this->_config->getEnvironment();
        $user = $config['user'];
        $to = $config['deploy-to'];
        $from = $config['deploy-from'];

        foreach ($config['hosts'] as $host) {            
            Magallanes_Console::output(PHP_TAB . 'Deploying to: ' . $host);
            $result = Magallanes_Task_Deploy_Rsync::exec($user, $host, $from, $to);
            if ($result == 0) {
                Magallanes_Console::output(PHP_TAB . 'OK' . PHP_EOL);
            } else {
                Magallanes_Console::output(PHP_TAB . 'FAIL' . PHP_EOL);
            }
        }
        
    }
}