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
        if ($this->_config->release('enabled', false) == true) {
            $releasesDirectory = $this->_config->release('directory', 'releases');
            $symlink = $this->_config->release('symlink', 'current');

            Mage_Console::output('Releases available on <dark_gray>' . $this->_config->getHost() . '</dark_gray>');
            
            $output = '';
            $result = $this->_runRemoteCommand('ls -1 ' . $releasesDirectory, $output);
            $releases = ($output == '') ? array() : explode(PHP_EOL, $output);
            
            if (count($releases) == 0) {
                Mage_Console::output('<dark_gray>No releases available</dark_gray> ... ', 2);
            } else {
                rsort($releases);
                $releases  = array_slice($releases, 0, 10);

                foreach ($releases as $releaseIndex => $release) {
                    $releaseIndex = str_pad($releaseIndex * -1, 2, ' ', STR_PAD_LEFT);
                    $releaseDate = $release[0] . $release[1] . $release[2] .$release[3]
                                 . '-'
                                 . $release[4] . $release[5]
                                 . '-'
                                 . $release[6] . $release[7]
                                 . ' '
                                 . $release[8] . $release[9]
                                 . ':'
                                 . $release[10] . $release[11]
                                 . ':'
                                 . $release[12] . $release[13];
                    
                    Mage_Console::output(
                        'Release: <purple>' . $release . '</purple> '
                      . '- Date: <dark_gray>' . $releaseDate . '</dark_gray> '
                      . '- Index: <dark_gray>' . $releaseIndex . '</dark_gray>', 2);                        
                }
            }

            return $result;
        } else {
            return false;
        }
    }

}