<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Alex V Kotelnikov <gudron@gudron.me>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task;

use Exception;

/**
 * Exception that indicates that the Task was Failed and rollback needed
 *
 * @author Alex V Kotelnikov <gudron@gudron.me>
 */
class RollbackException extends Exception
{
}
