<?php

declare(strict_types=1);

namespace Cel\Exception;

use Throwable;

/**
 * A marker interface for all exceptions thrown by the CEL library.
 *
 * This allows consumers to catch any exception originating from this library
 * with a single catch block.
 */
interface ExceptionInterface extends Throwable
{
}
