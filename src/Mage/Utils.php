<?php
/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage;

use Mage\Runtime\Runtime;
use DateTime;

/**
 * Utility class for resolving trivial operations
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class Utils
{
    /**
     * Given a stage code it will resolve a human friendly name
     *
     * @param string $stage
     * @return string
     */
    public static function getStageName($stage)
    {
        switch ($stage) {
            case Runtime::PRE_DEPLOY:
                return 'Pre Deployment';
                break;

            case Runtime::ON_DEPLOY:
                return 'On Deployment';
                break;

            case Runtime::POST_DEPLOY:
                return 'Post Deployment';
                break;

            case Runtime::ON_RELEASE:
                return 'On Release';
                break;

            case Runtime::POST_RELEASE:
                return 'Post Release';
                break;
        }

        return $stage;
    }

    /**
     * Given a Release ID, convert it to a DateTime instance
     *
     * @param string $releaseId The Release ID
     * @return DateTime
     */
    public static function getReleaseDate($releaseId)
    {
        $formatted = sprintf('%d%d%d%d-%d%d-%d%d %d%d:%d%d:%d%d',
            $releaseId[0],  $releaseId[1], $releaseId[2], $releaseId[3],
            $releaseId[4], $releaseId[5],
            $releaseId[6], $releaseId[7],
            $releaseId[8], $releaseId[9],
            $releaseId[10], $releaseId[11],
            $releaseId[12], $releaseId[13]
        );

        return new DateTime($formatted);
    }

    /**
     * Given a Date, calculate friendly how much time has passed
     *
     * @param DateTime $releaseDate
     * @return string
     */
    public static function getTimeDiff(DateTime $releaseDate)
    {
        $textDiff = '';
        $now = new DateTime();
        $diff = $now->diff($releaseDate);

        if ($diff->format('%a') <= 7) {
            if ($diff->format('%d') == 7) {
                $textDiff = 'a week ago';
            } elseif ($diff->format('%d') > 0 && $diff->format('%d') < 7) {
                $days = $diff->format('%d');
                if ($days <= 1) {
                    $textDiff = 'one day ago';
                } else {
                    $textDiff = $days . ' days ago';
                }
            } elseif ($diff->format('%d') == 0 && $diff->format('%h') > 0) {
                $hours = $diff->format('%h');
                if ($hours <= 1) {
                    $textDiff = 'one hour ago';
                } else {
                    $textDiff = $hours . ' hours ago';
                }
            } elseif ($diff->format('%d') == 0 && $diff->format('%h') == 0 && $diff->format('%i') > 0) {
                $minutes = $diff->format('%i');
                if ($minutes == 1) {
                    $textDiff = 'one minute ago';
                } else {
                    $textDiff = $minutes . ' minutes ago';
                }
            } elseif ($diff->format('%d') == 0 && $diff->format('%h') == 0 && $diff->format('%i') == 0) {
                $seconds = $diff->format('%s');
                if ($seconds < 10) {
                    $textDiff = 'just now';
                } else {
                    $textDiff = $seconds . ' seconds ago';
                }
            }
        }

        return $textDiff;
    }
}
