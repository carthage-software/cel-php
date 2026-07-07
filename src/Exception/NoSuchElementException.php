<?php

declare(strict_types=1);

namespace Cel\Exception;

use OutOfBoundsException;

/**
 * Thrown when accessing a sequence element at an index that does not exist.
 */
final class NoSuchElementException extends OutOfBoundsException implements ExceptionInterface {}
