<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Releases;

use Mage\Console;
use Mage\Task\AbstractTask;
use Mage\Task\Releases\IsReleaseAware;
use DateTime;

/**
 * Task for Listing Available Releases on an Environment
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ListTask extends AbstractTask implements IsReleaseAware
{
    public function getName()
    {
        return 'Listing releases [built-in]';
    }

    /**
     * List the Available Releases on an Environment
     * @see \Mage\Task\AbstractTask::run()
     */
    public function run()
    {
        if ($this->getConfig()->release('enabled', false) === true) {
            $releasesDirectory = $this->getConfig()->release('directory', 'releases');
            $symlink = $this->getConfig()->release('symlink', 'current');

            Console::output('Releases available on <bold>' . $this->getConfig()->getHost() . '</bold>');

            // Get Releases
            $output = '';
            $result = $this->runCommandRemote('ls -1 ' . $releasesDirectory, $output);
            $releases = ($output == '') ? array() : explode(PHP_EOL, $output);

            // Get Current
            $result = $this->runCommandRemote('ls -l ' . $symlink, $output) && $result;
            $currentRelease = explode('/', $output);
            $currentRelease = trim(array_pop($currentRelease));

            if (count($releases) == 0) {
                Console::output('<bold>No releases available</bold> ... ', 2);
            } else {
                rsort($releases);
                $releases = array_slice($releases, 0, 10);

                foreach ($releases as $releaseIndex => $release) {
                    $release = trim($release);
                    $releaseIndex = str_pad($releaseIndex * -1, 2, ' ', STR_PAD_LEFT);
                    $releaseDate = $release[0] . $release[1] . $release[2] . $release[3]
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

                    $dateDiff = $this->dateDiff($releaseDate);

                    Console::output(
                        'Release: <purple>' . $release . '</purple> '
                        . '- Date: <bold>' . $releaseDate . '</bold> '
                        . '- Index: <bold>' . $releaseIndex . '</bold>' . $dateDiff . $isCurrent, 2);
                }
            }

            Console::output('');
            return $result;
        } else {
            Console::output('');
            return false;
        }
    }

    /**
     * Calculates a Human Readable Time Difference
     * @param string $releaseDate
     * @return string
     */
    protected function dateDiff($releaseDate)
    {
        $textDiff = '';
        $releaseDate = new DateTime($releaseDate);
        $now = new DateTime();
        $diff = $now->diff($releaseDate);

        if ($diff->format('%a') <= 7) {
            if ($diff->format('%d') == 7) {
                $textDiff = ' [a week ago] ';
            } elseif ($diff->format('%d') > 0 && $diff->format('%d') < 7) {
                $days = $diff->format('%d');
                if ($days <= 1) {
                    $textDiff = ' [one day ago] ';
                } else {
                    $textDiff = ' [' . $days . ' days ago] ';
                }
            } elseif ($diff->format('%d') == 0 && $diff->format('%h') > 0) {
                $hours = $diff->format('%h');
                if ($hours <= 1) {
                    $textDiff = ' [one hour ago] ';
                } else {
                    $textDiff = ' [' . $hours . ' hours ago] ';
                }
            } elseif ($diff->format('%d') == 0 && $diff->format('%h') == 0) {
                $minutes = $diff->format('%i');
                if ($minutes <= 1) {
                    $textDiff = ' [one minute ago] ';
                } else {
                    $textDiff = ' [' . $minutes . ' minutes ago] ';
                }
            } elseif ($diff->format('%d') == 0 && $diff->format('%h') == 0 && $diff->format('%i') == 0) {
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
