<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\Newcraft\Symfony2;

use Mage\Task\BuiltIn\Symfony2\SymfonyAbstractTask;

/**
 * Task for Dumping Assetics
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class CopyParametersTask extends SymfonyAbstractTask
{
    /**
     * (non-PHPdoc)
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return 'Symfony v2 - Prepare parameters.yml [newcraft]';
    }

    /**
     * Dumps Assetics
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        $envName = $this->getConfig()->deployment('environment');

        //First check if parameters.yml exists
        $parametersYmlExistsCommand = $this->getReleasesAwareCommand('cd ./app/config && test -f parameters.yml && echo "y" || echo "n"');
        if( $this->runCommandRemote($parametersYmlExistsCommand, $output) && $output == 'y') {
            if($removeParametersCommand = $this->getReleasesAwareCommand('cd ./app/config && rm parameters.yml')) {
                return $this->copyParameters($envName);
            } else {
                return false;
            }
        } else {
            return $this->copyParameters($envName);
        }
    }

    /**
     * Copies the parameters-{envName}.yml to parameters.yml
     * @param $envName
     * @return bool
     */
    public function copyParameters($envName) {
        $environmentYmlExistsCommand = $this->getReleasesAwareCommand('cd ./app/config && test -f parameters-'.$envName.'.yml && echo "y" || echo "n"');
        if( $this->runCommandRemote($environmentYmlExistsCommand, $output) && $output == 'y') {
            return $this->getReleasesAwareCommand('cd ./app/config && cp -p parameters-'.$envName.'.yml parameters.yml');
        } else {
            return false;
        }
    }
}
