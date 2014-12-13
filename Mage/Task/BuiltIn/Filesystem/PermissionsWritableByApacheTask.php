<?php
namespace Mage\Task\BuiltIn\Filesystem;

/**
 * Task for giving Apache write permissions on given paths.
 *
 * Usage :
 *   pre-deploy:
 *     - filesystem/permissions-writable-by-apache: {paths: /var/www/myapp/app/cache:/var/www/myapp/app/cache, recursive: false, checkPathsExist: true}
 *   on-deploy:
 *     - filesystem/permissions-writable-by-apache: {paths: app/cache:app/logs, recursive: false, checkPathsExist: true}
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class PermissionsWritableByApacheTask extends PermissionsTask
{
    public function init()
    {
        parent::init();

        $this->setGroup('www-data')
             ->setRights('g+w');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Gives write permissions to Apache user for given paths [built-in]";
    }
}
