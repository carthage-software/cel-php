<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Util;

use BadMethodCallException;
use Decimal\Decimal;
use DomainException;
use TypeError;

use function method_exists;

/**
 * @internal
 */
final readonly class DecimalFactory
{
    /**
     * @throws BadMethodCallException If the decimal extension rejects the operation.
     * @throws DomainException If the value is not a valid decimal representation.
     * @throws TypeError If the value cannot be converted to a decimal.
     */
    public static function from(string $value, null|int $precision = null): Decimal
    {
        if (method_exists(Decimal::class, 'valueOf')) {
            /**
             * @mago-expect analysis:non-existent-method(2) - The `valueOf` method was added in version 2.0 of ext-decimal.
             *
             * @var Decimal
             */
            return $precision === null ? Decimal::valueOf($value) : Decimal::valueOf($value, $precision);
        }

        return $precision === null ? new Decimal($value) : new Decimal($value, $precision);
    }
}
