<?php

/*
 * This file is part of the Magallanes package.
 *
 * (c) Andrés Montañez <andres@andresmontanez.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mage\Task\Exception;

/**
 * The Task Failed, and it has a Custom Message
 *
 * @author Andrés Montañez <andresmontanez@gmail.com>
 */
class ErrorException extends \Exception
{
    public function getTrimmedMessage(int $maxLength = 60): string
    {
        $message = $this->getMessage();

        if (strlen($message) > $maxLength) {
            $message = substr($message, 0, $maxLength) . '...';
        }

        return $message;
    }
}
