<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Bms;

use Mage\Task\BuiltIn\Symfony2\SymfonyAbstractTask;

class RebuildLayoutTask extends SymfonyAbstractTask
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'BMS - Rebuild Layout [newcraft]';
    }

    /**
     * Installs Assets
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $command = $this->getAppPath() . ' bms:genLayout';
        $result = $this->runCommandRemote($command, $output);
        return $result;
    }
}
