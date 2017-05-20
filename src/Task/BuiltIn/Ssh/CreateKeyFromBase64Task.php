<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\BuiltIn\Ssh;

use Mage\Task\Exception\ErrorException;
use Mage\Task\AbstractTask;

/**
 * Ssh Task - Create key from base 64 encoded string
 *
 * @author Benjamin Gutmann <benjamin.gutmann@bestit-online.de>
 * @author Alexander Schneider <alexanderschneider85@gmail.com>
 */
class CreateKeyFromBase64Task extends AbstractTask
{
    public function getName()
    {
        return 'ssh/create-key-from-base64';
    }

    public function getDescription()
    {
        return '[SSH] Create ssh key from base64';
    }

    public function execute()
    {
        if (!array_key_exists('base64Key', $this->options)
            && !array_key_exists('base64KeyFromEnvVar', $this->options)
        ) {
            throw new ErrorException('Parameter "base64Key" or "base64KeyFromEnvVar" is required.');
        } elseif (array_key_exists('base64Key', $this->options)
            && array_key_exists('base64KeyFromEnvVar', $this->options)
        ) {
            throw new ErrorException('Either "base64Key" or "base64KeyFromEnvVar" can be set.');
        }

        $home = getenv('HOME');
        $sshDir = $home.DIRECTORY_SEPARATOR.'.ssh';
        
        if (!is_dir($sshDir)) {
            mkdir($sshDir);
        }
        
        $base64Key = isset($this->options['base64KeyFromEnvVar']) ?
            getenv($this->options['base64KeyFromEnvVar']) : $this->options['base64Key'];

        $keyFileName = isset($this->options['keyFileName']) ? $this->options['keyFileName'] : 'id_rsa';
        $sshKey = base64_decode($base64Key);
        $sshFile = $sshDir.DIRECTORY_SEPARATOR.$keyFileName;
        $success = (file_put_contents($sshFile, $sshKey) !== false);
        return ($success) ? chmod($sshFile, 0600) : false;
    }
}
