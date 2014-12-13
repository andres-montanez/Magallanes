<?php
namespace Mage\Task\BuiltIn\Filesystem;

/**
 * Task for giving Apache write permissions on given paths.
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class PermissionsWritableByApacheTask extends PermissionsTask
{
    public function init()
    {
        parent::init();

        $this->setGroup('www-data')
             ->setRights('775');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Gives write permissions to Apache user for given paths [built-in]";
    }
}