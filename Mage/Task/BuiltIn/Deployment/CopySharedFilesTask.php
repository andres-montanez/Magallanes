<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\Releases\SkipOnOverride;

class CopySharedFilesTask extends AbstractTask implements IsReleaseAware, SkipOnOverride
{
    public function getName()
    {
        return 'Copy files from shared folder [built-in]';
    }

    public function run()
    {
        $deployTo = $this->getConfig()->getDeployToDirectory();
        $sharedFolder = $this->getConfig()->getOption('environment.deployment.shared_folder', 'shared');
        $this->runJobRemote("cp -r $deployTo/../$sharedFolder/* $deployTo --verbose");

        return $this->isAllOk();
    }
}
