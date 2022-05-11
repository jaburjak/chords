<?php
/**
 * @author Jakub Jabůrek <jaburek.jakub@gmail.com>
 */

declare(strict_types=1);

namespace Chords\Exception;

use RuntimeException;

/**
 * The input XML document is malformed.
 *
 * @package Chords\Exception
 */
class InvalidXmlException extends RuntimeException {
}