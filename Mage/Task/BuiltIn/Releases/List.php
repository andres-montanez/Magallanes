<?php
class Mage_Task_BuiltIn_Releases_List
    extends Mage_Task_TaskAbstract
{
    public function getName()
    {
        return 'Listing releases [built-in]';
    }

    public function run()
    {
        if (isset($this->_config['deploy']['releases']['enabled'])) {
            if ($this->_config['deploy']['releases']['enabled'] == 'true') {
                if (isset($this->_config['deploy']['releases']['directory'])) {
                    $releasesDirectory = $this->_config['deploy']['releases']['directory'];
                } else {
                    $releasesDirectory = 'releases';
                }
                if (isset($this->_config['deploy']['releases']['symlink'])) {
                    $symlink = $this->_config['deploy']['releases']['symlink'];
                } else {
                    $symlink = 'current';
                }

                Mage_Console::output('Releases available on <dark_gray>' . $this->_config['deploy']['host'] . '</dark_gray>');
                
                $output = '';
                $result = $this->_runRemoteCommand('ls -1 ' . $releasesDirectory, $output);
                $releases = ($output == '') ? array() : explode(PHP_EOL, $output);
                
                if (count($releases) == 0) {
                    Mage_Console::output('<dark_gray>No releases available</dark_gray> ... ', 2);
                } else {
                    rsort($releases);
                    foreach ($releases as $releaseIndex => $releaseDate) {
                        Mage_Console::output('Index: ' . $releaseIndex . ' - <purple>' . $releaseDate . '</purple>', 2);                        
                    }
                }

                return $result;

            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}