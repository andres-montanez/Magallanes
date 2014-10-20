<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE_YAML
 * file that was distributed with this source code.
 */

namespace Mage\Yaml\Exception;

use Mage\Yaml\Exception\ExceptionInterface;

/**
 * Exception class thrown when an error occurs during parsing.
 *
 * @author Romain Neutron <imprec@gmail.com>
 *
 * @api
 */
class RuntimeException extends \RuntimeException implements ExceptionInterface
{
}
