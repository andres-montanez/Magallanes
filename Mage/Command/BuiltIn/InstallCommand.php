<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command\BuiltIn;

use Mage\Command\AbstractCommand;
use Mage\Console;

/**
 * Installs Magallanes in the Local System
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class InstallCommand extends AbstractCommand
{
    /**
     * Installs Magallanes
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        $exitCode = 88;
        Console::output('Installing <bold>Magallanes</bold>... ', 1, 0);

        // Vars
        $installDir = $this->getConfig()->getParameter('installDir', '/opt/magallanes');
        $systemWide = $this->getConfig()->getParameter('systemWide', false);

        // Clean vars
        $baseDir = realpath(dirname($installDir));
        $installDir = basename($installDir);

        // Check if install dir is available
        if (!is_dir($baseDir) || !is_writable($baseDir)) {
            Console::output('<red>Failure: install directory is invalid.</red>', 0, 2);

            // Chck if it is a system wide install the user is root
        } elseif ($systemWide && (getenv('LOGNAME') != 'root')) {
            Console::output('<red>Failure: you have to be root to perform a system wide install.</red>', 0, 2);
        } else {
            $destinationDir = $baseDir . '/' . $installDir;
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir);
            }

            // Copy
            $this->recursiveCopy(MAGALLANES_DIRECTORY, $destinationDir . '/' . MAGALLANES_VERSION);

            // Check if there is already a symlink
            if (file_exists($destinationDir . '/' . 'latest')
                && is_link($destinationDir . '/' . 'latest')
            ) {
                unlink($destinationDir . '/' . 'latest');
            }

            // Create "latest" symlink
            symlink(
                $destinationDir . '/' . MAGALLANES_VERSION,
                $destinationDir . '/' . 'latest'
            );
            chmod($destinationDir . '/' . MAGALLANES_VERSION . '/bin/mage', 0755);

            if ($systemWide) {
                if (!file_exists('/usr/bin/mage')) {
                    symlink($destinationDir . '/latest/bin/mage', '/usr/bin/mage');
                }
            }

            Console::output('<light_green>Success!</light_green>', 0, 2);
            $exitCode = 0;
        }

        return $exitCode;
    }

    /**
     * Copy Files
     * @param string $from
     * @param string $to
     * @return boolean
     */
    protected function recursiveCopy($from, $to)
    {
        if (is_dir($from)) {
            mkdir($to);
            $files = scandir($from);

            if (count($files) > 0) {
                foreach ($files as $file) {
                    if (strpos($file, '.') === 0) {
                        continue;
                    }

                    if (is_dir($from . DIRECTORY_SEPARATOR . $file)) {
                        $this->recursiveCopy(
                            $from . DIRECTORY_SEPARATOR . $file,
                            $to . DIRECTORY_SEPARATOR . $file
                        );
                    } else {
                        copy(
                            $from . DIRECTORY_SEPARATOR . $file,
                            $to . DIRECTORY_SEPARATOR . $file
                        );
                    }
                }
            }
            return true;
        } elseif (is_file($from)) {
            return copy($from, $to);
        } else {
            return false;
        }
    }
}
