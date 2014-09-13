<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Composer;

use Mage\Task\AbstractTask;

/**
 * Abstract Task for Composer
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
abstract class ComposerAbstractTask extends AbstractTask
{
    protected function getComposerCmd()
    {
        $composerCmd = $this->getParameter('composer_cmd', $this->getConfig()->general('composer_cmd', 'php composer.phar'));
        return $this->getConfig()->general('composer_cmd', $composerCmd);
    }
}
