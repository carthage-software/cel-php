<?php

declare(strict_types=1);

namespace Cel\Exception;

use Cel\Span\Span;
use Cel\Value\Value;

use function sprintf;

/**
 * Thrown when an operation is not supported for the given types.
 */
final class UnsupportedOperationException extends EvaluationException
{
    public static function forComparison(
        Value $that,
        Value $other,
        Span $span = new Span(0, 0),
    ): UnsupportedOperationException {
        return new static(
            sprintf('Cannot compare values of type `%s` and `%s`', $that->getType(), $other->getType()),
            $span,
        );
    }

    public static function forNaN(Span $span = new Span(0, 0)): UnsupportedOperationException
    {
        return new static('NaN values cannot be ordered', $span);
    }
}
