<?php

declare(strict_types=1);

namespace Cel\Exception;

use OutOfRangeException as RootOutOfRangeException;

use function sprintf;

/**
 * Thrown when a search offset falls outside the bounds of the subject string.
 *
 * @api
 */
final class OutOfRangeException extends RootOutOfRangeException implements ExceptionInterface
{
    public static function forOffset(int $offset): self
    {
        return new self(sprintf('Offset %d is out of bounds', $offset));
    }
}
