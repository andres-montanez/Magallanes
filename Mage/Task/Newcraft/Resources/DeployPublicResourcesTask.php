<?php

namespace Mage\Task\Newcraft\Resources;

use Mage\Task\AbstractTask;
use Mage\Task\SkipException;

/**
 * Class RemoveCurrentDirectoryTask
 * @package Mage\Task\Newcraft\Filesystem
 */
class DeployPublicResourcesTask extends AbstractTask
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'deploying public resources to production [newcraft]';
    }

    /**
     * Runs NPN build-prod task that should in turn trigger all required preparation tasks
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        if('github-download' !== $this->getConfig()->deployment('strategy')){
            throw new SkipException();
        }

        $basePath = rtrim($this->getParameter('basepath', 'src/App/TelBundle/Resources/public'), '/');

        $files = $this->getParameter('files', []);

        $todo = [];
        $skipped = [];
        foreach($files as $file){
            $this->runCommandLocal('git check-ignore ' . $basePath . '/' . $file . ' | wc -l ',$isIgnored);
            if(true === (bool) $isIgnored){
                $todo[] = $basePath . '/' . $file;
            } else {
                $skipped[] = $file;
            }
        }

        if(0 < count($skipped)) {
            Console::output('');
            foreach($skipped as $file){
                Console::output('skipping <white>' . $file . '</white>: ', 3, 0);
            }
            Console::output('Cont... <purple>' . $this->getName() . '</purple>.... ', 2, 0);
        }

        if(0 === count($todo)) {
            throw new SkipException();
        }

        $compressCommand = 'tar czf - '.implode(' ',$todo);
        $extractCommand = 'tar xzf -';

        return $this->runCommandLocal($compressCommand . ' | ' . $this->getCommandRemote($extractCommand));
    }
}
