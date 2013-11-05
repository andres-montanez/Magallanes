<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

abstract class Mage_Command_CommandAbstract
{
    protected $_config = null;

    public abstract function run();

    public function setConfig(Mage_Config $config)
    {
        $this->_config = $config;
    }

    /**
     *
     * @return Mage_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
}