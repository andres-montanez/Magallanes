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
use DateInterval;

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
    public function getStageName($stage)
    {
        switch ($stage) {
            case Runtime::PRE_DEPLOY:
                return 'Pre Deploy';
                break;

            case Runtime::ON_DEPLOY:
                return 'On Deploy';
                break;

            case Runtime::POST_DEPLOY:
                return 'Post Deploy';
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
    public function getReleaseDate($releaseId)
    {
        $formatted = sprintf('%d%d%d%d-%d%d-%d%d %d%d:%d%d:%d%d',
            $releaseId[0], $releaseId[1], $releaseId[2], $releaseId[3],
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
    public function getTimeDiff(DateTime $releaseDate)
    {
        $now = new DateTime();

        /** @var DateInterval $diff */
        $diff = $now->diff($releaseDate);

        if ($diff->days > 7) {
            return '';
        }

        if ($diff->days == 7) {
            return 'a week ago';
        }

        if ($diff->days > 1) {
            return sprintf('%d days ago', $diff->days);
        }

        if ($diff->days == 1) {
            return 'one day ago';
        }

        if ($diff->h > 1) {
            return sprintf('%d hours ago', $diff->h);
        }

        if ($diff->h == 1) {
            return 'one hour ago';
        }

        if ($diff->i > 1) {
            return sprintf('%d minutes ago', $diff->i);
        }

        if ($diff->i == 1) {
            return 'one minute ago';
        }

        if ($diff->s >= 10) {
            return sprintf('%d seconds ago', $diff->s);
        }

        return 'just now';
    }
}
