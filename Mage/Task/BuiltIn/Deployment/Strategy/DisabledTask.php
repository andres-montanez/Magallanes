<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Deployment\Strategy;

use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use Mage\Task\SkipException;

/**
 * Deployment Strategy is Disabled
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class DisabledTask extends AbstractTask implements IsReleaseAware
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Disabled Deployment [built-in]';
    }

    /**
     * Deployment Strategy is Disabled
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        throw new SkipException;
    }
}
