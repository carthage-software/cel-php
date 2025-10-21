<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Function;

use Cel\Extension\Decimal\Function\Handler\FromFloatHandler;
use Cel\Extension\Decimal\Function\Handler\FromIntegerHandler;
use Cel\Extension\Decimal\Function\Handler\FromStringHandler;
use Cel\Extension\Decimal\Function\Handler\FromStringWithPrecisionHandler;
use Cel\Extension\Decimal\Function\Handler\FromUnsignedIntegerHandler;
use Cel\Function\FunctionInterface;
use Cel\Value\ValueKind;
use Override;

/**
 * Creates a Decimal number from a string, integer, or float.
 *
 * Function signatures:
 * - decimal(string) -> DecimalNumber
 * - decimal(int) -> DecimalNumber
 * - decimal(uint) -> DecimalNumber
 * - decimal(float) -> DecimalNumber
 * - decimal(string, int) -> DecimalNumber (with precision)
 */
final readonly class DecimalFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'decimal';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        // decimal(string) -> DecimalNumber
        yield [ValueKind::String] => new FromStringHandler();

        // decimal(int) -> DecimalNumber
        yield [ValueKind::Integer] => new FromIntegerHandler();

        // decimal(uint) -> DecimalNumber
        yield [ValueKind::UnsignedInteger] => new FromUnsignedIntegerHandler();

        // decimal(float) -> DecimalNumber
        yield [ValueKind::Float] => new FromFloatHandler();

        // decimal(string, int) -> DecimalNumber (with precision)
        yield [ValueKind::String, ValueKind::Integer] => new FromStringWithPrecisionHandler();
    }
}
