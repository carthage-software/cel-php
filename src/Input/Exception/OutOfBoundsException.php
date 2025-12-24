<?php

declare(strict_types=1);

namespace Cel\Input\Exception;

use OutOfBoundsException as RootOutOfBoundsException;

/**
 * Thrown when an operation attempts to access a position outside the valid bounds of the input.
 */
final class OutOfBoundsException extends RootOutOfBoundsException implements ExceptionInterface {}
