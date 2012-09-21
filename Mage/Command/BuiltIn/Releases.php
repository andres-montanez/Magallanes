<?php
class Mage_Task_Releases
{
    private $_config = null;
    private $_action = null;
    private $_release = null;

    public function setAction($action)
    {
        $this->_action = $action;
        return $this;
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function setRelease($releaseId)
    {
        $this->_release = $releaseId;
        return $this;
    }

    public function getRelease()
    {
        return $this->_release;
    }

    public function run(Mage_Config $config)
    {
        $this->_config = $config;

        if ($config->getEnvironmentName() == '') {
            Mage_Console::output('<red>You must specify an environment</red>', 0, 2);
            return;
        }

        $lockFile = '.mage/' . $config->getEnvironmentName() . '.lock';
        if (file_exists($lockFile)) {
            Mage_Console::output('<red>This environment is locked!</red>', 0, 2);
            return;
        }

        // Run Tasks for Deployment
        $hosts = $config->getHosts();

        if (count($hosts) == 0) {
            Mage_Console::output('<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, unable to get releases.</dark_gray>', 1, 3);

        } else {
            foreach ($hosts as $host) {
                $config->setHost($host);
                switch ($this->getAction()) {
                    case 'list':
                        $task = Mage_Task_Factory::get('releases/list', $config);
                        $task->init();
                        $result = $task->run();
                        break;

                    case 'rollback':
                        $task = Mage_Task_Factory::get('releases/rollback', $config);
                        $task->init();
                        $task->setRelease($this->getRelease());
                        $result = $task->run();
                        break;
                }
            }
        }
    }
}