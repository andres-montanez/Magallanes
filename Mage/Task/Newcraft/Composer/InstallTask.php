<?php

namespace Mage\Task\Newcraft\Composer;

use Mage\Task\BuiltIn\Composer\ComposerAbstractTask;
use Mage\Task\ErrorWithMessageException;
use Mage\Console;

class InstallTask extends ComposerAbstractTask
{
    /**
     * Returns the Title of the Task
     * @return string
     */
    public function getName()
    {
        return 'Install vendors via Composer [newcraft]';
    }

    /**
     * Runs the task
     *
     * @return boolean
     * @throws ErrorWithMessageException
     */
    public function run()
    {
        $dev = $this->getParameter('dev', true);

        if($this->getComposerCmd() === 'php composer.phar'){
            $downloadCommand = 'curl -sS https://getcomposer.org/installer | php';
            $composerCommand = 'test -f composer.phar && test -f composer.phar || '.$downloadCommand.'; '.$this->getComposerCmd();
            Console::output('<purple>Adding dl command</purple> ... ', 0, 0);
        } else {
            $composerCommand = $this->getComposerCmd();
        }


        return $this->runCommandRemote($composerCommand . ' install --no-interaction' . ($dev ? ' --dev' : ' --no-dev --optimize-autoloader'));
    }
}
