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
	 * Source for downloading
	 * @var string
	 */
    const DOWNLOAD = 'http://download.magephp.com/magallanes.{version}.tar.gz';

    /**
     * JSON for Upgrade
     * @var string
     */
    const UPGRADE = 'http://download.magephp.com/upgrade.json';

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
        $owner = posix_getpwuid(fileowner(__FILE__));
        $owner = $owner['name'];

        if ($user != 'root' && $user != $owner) {
            Console::output('<red>FAIL</red>', 0, 1);
            Console::output('You need to be the <dark_gray>' . $owner . '</dark_gray> user to perform the upgrade, or <dark_gray>root</dark_gray>.', 2);

        } else {
        	// Check version
        	$version = json_decode(file_get_contents(self::UPGRADE));

        	if ($version !== false) {
        		$versionCompare = version_compare(MAGALLANES_VERSION, $version->latest);
        		if ($versionCompare == 0) {
        			Console::output('<yellow>SKIP</yellow>', 0, 1);
        			Console::output('Your current version is up to date.', 2);

        		} else if ($versionCompare == 1) {
        			Console::output('<yellow>SKIP</yellow>', 0, 1);
        			Console::output('Your current version is newer.', 2);

        		} else if ($versionCompare == -1) {
        			// Download Package
        			$tarball = file_get_contents(str_replace('{version}', $version->latest, self::DOWNLOAD));
        			if ($tarball === false) {
        				Console::output('<red>FAIL</red>', 0, 1);
        				Console::output('Corrupted download.', 2);

        			} else {
        				$tarballFile = tempnam('/tmp', 'magallanes_download');
        				rename($tarballFile, $tarballFile . '.tar.gz');
        				$tarballFile .= '.tar.gz';
        				file_put_contents($tarballFile, $tarball);

        				Console::executeCommand('rm -rf ' . MAGALLANES_DIRECTORY);
        				Console::executeCommand('cd ' . dirname($tarballFile) . ' && tar xfz ' . $tarballFile);
        				Console::executeCommand('mv ' . dirname($tarballFile) . '/magallanes ' . MAGALLANES_DIRECTORY);

        				Console::output('<green>OK</green>', 0, 1);
        			}

        		} else {
        			Console::output('<red>FAIL</red>', 0, 1);
        			Console::output('Invalid version.', 2);
        		}
        	} else {
        		Console::output('<red>FAIL</red>', 0, 1);
        		Console::output('Invalid version.', 2);
        	}
        }
    }
}