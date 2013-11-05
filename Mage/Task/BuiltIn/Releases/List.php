<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

class Mage_Task_BuiltIn_Releases_List
    extends Mage_Task_TaskAbstract
    implements Mage_Task_Releases_BuiltIn
{
    public function getName()
    {
        return 'Listing releases [built-in]';
    }

    public function run()
    {
        if ($this->getConfig()->release('enabled', false) == true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            Mage_Console::output('Releases available on <dark_gray>' . $this->getConfig()->getHost() . '</dark_gray>');

            // Get Releases
            $output = '';
            $result = $this->_runRemoteCommand('ls -1 ' . $releasesDirectory, $output);
            $releases = ($output == '') ? array() : explode(PHP_EOL, $output);

            // Get Current
            $result = $this->_runRemoteCommand('ls -l ' . $symlink, $output);
            $currentRelease = explode('/', $output);
            $currentRelease = trim(array_pop($currentRelease));

            if (count($releases) == 0) {
                Mage_Console::output('<dark_gray>No releases available</dark_gray> ... ', 2);
            } else {
                rsort($releases);
                $releases  = array_slice($releases, 0, 10);

                foreach ($releases as $releaseIndex => $release) {
                    $release = trim($release);
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

                    $isCurrent = '';
                    if ($currentRelease == $release) {
                        $isCurrent = ' <- current';
                    }

                    $dateDiff = $this->_dateDiff($releaseDate);

                    Mage_Console::output(
                        'Release: <purple>' . $release . '</purple> '
                      . '- Date: <dark_gray>' . $releaseDate . '</dark_gray> '
                      . '- Index: <dark_gray>' . $releaseIndex . '</dark_gray>' . $dateDiff . $isCurrent, 2);
                }
            }

            Mage_Console::output('');
            return $result;

        } else {
            Mage_Console::output('');
            return false;
        }
    }

    private function _dateDiff($releaseDate)
    {
        $textDiff = '';
        $releaseDate = new DateTime($releaseDate);
        $now = new DateTime();
        $diff = $now->diff($releaseDate);

        if ($diff->format('%a') <= 7) {
            if ($diff->format('%d') == 7) {
                $textDiff = ' [a week ago] ';

            } else if ($diff->format('%d') > 0 && $diff->format('%d') < 7) {
                $days = $diff->format('%d');
                if ($days <= 1) {
                    $textDiff = ' [one day ago] ';
                } else {
                    $textDiff = ' [' . $days . ' days ago] ';
                }

            } else if ($diff->format('%d') == 0 && $diff->format('%h') > 0) {
                $hours = $diff->format('%h');
                if ($hours <= 1) {
                    $textDiff = ' [one hour ago] ';
                } else {
                    $textDiff = ' [' . $hours . ' hours ago] ';
                }

            } else if ($diff->format('%d') == 0 && $diff->format('%h') == 0) {
                $minutes = $diff->format('%i');
                if ($minutes <= 1) {
                    $textDiff = ' [one minute ago] ';
                } else {
                    $textDiff = ' [' . $minutes . ' minutes ago] ';
                }

            } else if ($diff->format('%d') == 0 && $diff->format('%h') == 0 && $diff->format('%i') == 0) {
                $seconds = $diff->format('%s');
                if ($seconds < 10) {
                    $textDiff = ' [just now!] ';
                } else {
                    $textDiff = ' [' . $seconds . ' seconds ago] ';
                }
            }
        }

        return $textDiff;
    }

}