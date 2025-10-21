<?php

declare(strict_types=1);

namespace Cel\Exception;

use Cel\Span\Span;
use Cel\Value\Value;
use Psl\Str;

/**
 * Thrown when an operation is not supported for the given types.
 */
final class UnsupportedOperationException extends EvaluationException
{
    public static function forEquality(
        Value $that,
        Value $other,
        Span $span = new Span(0, 0),
    ): UnsupportedOperationException {
        return new static(
            Str\format('Cannot compare values of type `%s` and `%s` for equality', $that->getType(), $other->getType()),
            $span,
        );
    }

    public static function forComparison(
        Value $that,
        Value $other,
        Span $span = new Span(0, 0),
    ): UnsupportedOperationException {
        return new static(
            Str\format('Cannot compare values of type `%s` and `%s`', $that->getType(), $other->getType()),
            $span,
        );
    }
}
