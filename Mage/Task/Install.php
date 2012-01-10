<?php
class Mage_Task_Install
{
    public function run ()
    {
        Mage_Console::output('Installing <dark_gray>Magallanes</dark_gray>... ', 1, 0);
        $this->_recursiveCopy('./', '/opt/magallanes-' . MAGALLANES_VERSION);
        
        if (file_exists('/opt/magallanes') && is_link('/opt/magallanes')) {
            unlink('/opt/magallanes');
        }
        symlink('/opt/magallanes-' . MAGALLANES_VERSION, '/opt/magallanes');
        chmod('/opt/magallanes/bin/mage', 0755);
        if (!file_exists('/usr/bin/mage')) {
            symlink('/opt/magallanes/bin/mage', '/usr/bin/mage');
        }

        Mage_Console::output('<light_green>Success!</light_green>', 0, 2);
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