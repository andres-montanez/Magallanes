<?php
namespace Mage\Task\BuiltIn\Compose;

use Mage\Task\AbstractTask;
use Mage\Task\ErrorWithMessageException;

class ComposerInstallTask extends AbstractTask
{
    public function getName()
    {
        return 'composer install';
    }

    public function run()
    {
        $j = $this->runJobRemote("`which composer` install --no-dev --optimize-autoloader");

        if ($j->failed()) {
            throw new ErrorWithMessageException(implode("\n", array_merge($j->stdout , $j->stderr)));
        }
        return true;
    }
}