<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal;

use Decimal\Decimal;

use function method_exists;

final readonly class DecimalFactory
{
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
