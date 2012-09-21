<?php
class Mage_Task_Upgrade
{
    const DOWNLOAD = 'https://github.com/andres-montanez/Magallanes/tarball/stable';
    
    public function run ()
    {
        Mage_Console::output('Upgrading <dark_gray>Magallanes</dark_gray> ... ', 1, 0);
        
        $user = '';
        // Check if user is root
        Mage_Console::executeCommand('whoami', $user);
        if ($user != 'root') {
            Mage_Console::output('<red>FAIL</red>', 0, 1);
            Mage_Console::output('You need to be the <dark_gray>root</dark_gray> user to perform the upgrade.', 2);
            
        } else {
            // Download Package
            $tarball = file_get_contents(self::DOWNLOAD);
            $tarballFile = tempnam('/tmp', 'magallanes_download');
            rename($tarballFile, $tarballFile . '.tar.gz');
            $tarballFile .= '.tar.gz';
            file_put_contents($tarballFile, $tarball);
            
            // Unpackage
            if (file_exists('/tmp/__magallanesDownload')) {
                Mage_Console::executeCommand('rm -rf /tmp/__magallanesDownload');
            }
            Mage_Console::executeCommand('mkdir /tmp/__magallanesDownload');
            Mage_Console::executeCommand('cd /tmp/__magallanesDownload && tar xfz ' . $tarballFile);
            Mage_Console::executeCommand('rm -f ' . $tarballFile);
            
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
                    Mage_Console::output('<yellow>SKIP</yellow>', 0, 1);
                    Mage_Console::output('Your current version is up to date.', 2);

                } else if ($versionCompare > 0) {
                    Mage_Console::output('<yellow>SKIP</yellow>', 0, 1);
                    Mage_Console::output('Your current version is newer.', 2);

                } else {
                    $this->_recursiveCopy('/tmp/__magallanesDownload/' . $packageDir, '/opt/magallanes-' . $version);
                    unlink('/opt/magallanes');
                    symlink('/opt/magallanes-' . $version, '/opt/magallanes');
                    chmod('/opt/magallanes/bin/mage', 0755);

                    Mage_Console::output('<green>OK</green>', 0, 1);
                }
                
            } else {
                Mage_Console::output('<red>FAIL</red>', 0, 1);
                Mage_Console::output('Corrupted download.', 2);
            }
        }
        

    }

    private function _recursiveCopy ($from, $to)
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
                        $this->_recursiveCopy(
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