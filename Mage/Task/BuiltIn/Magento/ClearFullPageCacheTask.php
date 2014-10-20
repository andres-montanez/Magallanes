<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Magento;

use Mage\Task\AbstractTask;

/**
 * Task for Clearing Full Page Cache
 *
 * @author Oscar Reales <oreales@gmail.com>
 */
class ClearFullPageCacheTask extends AbstractTask
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Magento - Clean Full Page Cache [built-in]';
    }

    /**
     * Clears Full Page Cache
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $command = 'rm -rf var/full_page_cache/*';
        $result = $this->runCommand($command);

        return $result;
    }
}
