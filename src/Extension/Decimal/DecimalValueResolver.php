<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal;

use Cel\Value\MessageValue;
use Cel\Value\Resolver\ValueResolverInterface;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;

use function assert;

/**
 * Value resolver for Decimal\Decimal instances.
 *
 * Converts Decimal\Decimal to DecimalNumber (wrapped as MessageValue) for use in CEL expressions.
 */
final readonly class DecimalValueResolver implements ValueResolverInterface
{
    #[Override]
    public function canResolve(mixed $value): bool
    {
        return $value instanceof Decimal;
    }

    #[Override]
    public function resolve(mixed $value): Value
    {
        assert($value instanceof Decimal, 'Value must be an instance of Decimal\Decimal');
        return new MessageValue(new DecimalNumber($value), []);
    }
}
