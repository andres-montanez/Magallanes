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

/**
 * Utility class for resolving trivial operations
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class Utils
{
    /**
     * Given a stage code it will resolve a human friendly name
     */
    public function getStageName(string $stage): string
    {
        switch ($stage) {
            case Runtime::PRE_DEPLOY:
                return 'Pre Deploy';

            case Runtime::ON_DEPLOY:
                return 'On Deploy';

            case Runtime::POST_DEPLOY:
                return 'Post Deploy';

            case Runtime::ON_RELEASE:
                return 'On Release';

            case Runtime::POST_RELEASE:
                return 'Post Release';
        }

        return $stage;
    }

    /**
     * Given a Release ID, convert it to a DateTime instance
     */
    public function getReleaseDate(string $releaseId): \DateTime
    {
        $formatted = sprintf(
            '%d%d%d%d-%d%d-%d%d %d%d:%d%d:%d%d',
            $releaseId[0],
            $releaseId[1],
            $releaseId[2],
            $releaseId[3],
            $releaseId[4],
            $releaseId[5],
            $releaseId[6],
            $releaseId[7],
            $releaseId[8],
            $releaseId[9],
            $releaseId[10],
            $releaseId[11],
            $releaseId[12],
            $releaseId[13]
        );

        return new \DateTime($formatted);
    }

    /**
     * Given a Date, calculate friendly how much time has passed
     */
    public function getTimeDiff(\DateTime $releaseDate): string
    {
        $now = new \DateTime();
        $diff = $now->diff($releaseDate);

        if ($diff->days > 7) {
            return '';
        }

        if ($diff->days == 7) {
            return 'a week ago';
        }

        if ($diff->days >= 1) {
            return sprintf('%d day(s) ago', $diff->days);
        }

        if ($diff->h >= 1) {
            return sprintf('%d hour(s) ago', $diff->h);
        }

        if ($diff->i >= 1) {
            return sprintf('%d minute(s) ago', $diff->i);
        }

        return 'just now';
    }
}
