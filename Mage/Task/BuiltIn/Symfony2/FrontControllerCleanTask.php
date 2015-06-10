<?php
namespace Task;

use Mage\Task\AbstractTask;

/**
 * @author Muhammad Surya Ihsanuddin <surya.kejawen@gmail.com>
 */
class FrontControllerCleanTask extends AbstractTask
{
    public function getName()
    {
        return 'Cleaning Project';
    }

    public function run()
    {
        $command = 'rm -rf web/app_*.php';
        $result = $this->runCommandRemote($command);

        return $result;
    }
}
