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

use Exception;

/**
 * Upgrades the Magallanes Version on the Local System
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class UpgradeCommand extends InstallCommand
{
	/**
	 * GIT Source for downloading
	 * @var string
	 */
    const DOWNLOAD = 'https://github.com/andres-montanez/Magallanes/tarball/stable';

    /**
     * Command for Upgrading Magallanes
     * @see \Mage\Command\BuiltIn\InstallCommand::run()
     */
    public function run()
    {
        Console::output('Upgrading <dark_gray>Magallanes</dark_gray> ... ', 1, 0);

        $user = '';
        // Check if user is root
        Console::executeCommand('whoami', $user);
        if ($user != 'root') {
            Console::output('<red>FAIL</red>', 0, 1);
            Console::output('You need to be the <dark_gray>root</dark_gray> user to perform the upgrade.', 2);

        } else {
            // Download Package
            $tarball = file_get_contents(self::DOWNLOAD);
            $tarballFile = tempnam('/tmp', 'magallanes_download');
            rename($tarballFile, $tarballFile . '.tar.gz');
            $tarballFile .= '.tar.gz';
            file_put_contents($tarballFile, $tarball);

            // Unpackage
            if (file_exists('/tmp/__magallanesDownload')) {
                Console::executeCommand('rm -rf /tmp/__magallanesDownload');
            }
            Console::executeCommand('mkdir /tmp/__magallanesDownload');
            Console::executeCommand('cd /tmp/__magallanesDownload && tar xfz ' . $tarballFile);
            Console::executeCommand('rm -f ' . $tarballFile);

            // Find Package
            $tarballDir = opendir('/tmp/__magallanesDownload');
            while (($file = readdir($tarballDir)) == true) {
                if ($file == '.' || $file == '..') {
                    continue;
                } else {
                    $packageDir = $file;
                    break;
                }
            }

            // Get Version
            $version = false;
            if (file_exists('/tmp/__magallanesDownload/' . $packageDir . '/bin/mage')) {
                list(, $version) = file('/tmp/__magallanesDownload/' . $packageDir . '/bin/mage');
                $version = trim(str_replace('#VERSION:', '', $version));
            }

            if ($version != false) {
                $versionCompare = version_compare(MAGALLANES_VERSION, $version);
                if ($versionCompare == 0) {
                    Console::output('<yellow>SKIP</yellow>', 0, 1);
                    Console::output('Your current version is up to date.', 2);

                } else if ($versionCompare > 0) {
                    Console::output('<yellow>SKIP</yellow>', 0, 1);
                    Console::output('Your current version is newer.', 2);

                } else {
                    $this->recursiveCopy('/tmp/__magallanesDownload/' . $packageDir, '/opt/magallanes-' . $version);
                    unlink('/opt/magallanes');
                    symlink('/opt/magallanes-' . $version, '/opt/magallanes');
                    chmod('/opt/magallanes/bin/mage', 0755);

                    Console::output('<green>OK</green>', 0, 1);
                }

            } else {
                Console::output('<red>FAIL</red>', 0, 1);
                Console::output('Corrupted download.', 2);
            }
        }


    }
}