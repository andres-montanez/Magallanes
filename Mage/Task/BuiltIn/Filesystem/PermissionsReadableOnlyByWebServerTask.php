<?php
namespace Mage\Task\BuiltIn\Filesystem;

use Mage\Task\SkipException;

/**
 * Task for giving only to web server read permissions on given paths.
 *
 * Usage :
 *   pre-deploy:
 *     - filesystem/permissions-readable-only-by-web-server: {paths: /var/www/myapp/app/config/config.yml:/var/www/myapp/app/config/parameters.yml, recursive: false, checkPathsExist: true}
 *     - filesystem/permissions-readable-only-by-web-server:
 *         paths:
 *             - /var/www/myapp/app/config/config.yml
 *             - /var/www/myapp/app/config/parameters.yml
 *         recursive: false
 *         checkPathsExist: true
 *   on-deploy:
 *     - filesystem/permissions-readable-only-by-web-server: {paths: app/config/config.yml:app/config/parameters.yml, recursive: false, checkPathsExist: true}
 *
 * @author Jérémy Huet <jeremy.huet@gmail.com>
 */
class PermissionsReadableOnlyByWebServerTask extends PermissionsTask
{
    /**
     * Set group with web server user and give group write permissions.
     */
    public function init()
    {
        parent::init();

        $this->setGroup($this->getParameter('group') ? $this->getParameter('group') : $this->getWebServerUser())
             ->setRights('040');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Giving read permissions only to web server user for given paths [built-in]";
    }

    /**
     * Tries to guess the web server user by going thru the running processes.
     *
     * @return string
     * @throws SkipException
     */
    protected function getWebServerUser()
    {
        $this->runCommand("ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1", $webServerUser);

        if (empty($webServerUser)) {
            throw new SkipException("Can't guess web server user. Please check if it is running or force it by setting the group parameter");
        }

        return $webServerUser;
    }
}
