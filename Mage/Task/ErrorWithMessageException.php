<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task;

use Exception;

/**
 * Exception that indicates that the Task has an Error and also a Message indicating the Error
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ErrorWithMessageException extends Exception
{
}
