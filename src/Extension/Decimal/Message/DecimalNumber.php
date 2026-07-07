<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Message;

use Cel\Exception\InternalException;
use Cel\Message\MessageInterface;
use Cel\Message\ZeroValueInterface;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Decimal\Decimal;
use Override;

/**
 * Wrapper class for Decimal\Decimal that implements MessageInterface.
 *
 * This allows Decimal numbers to be used seamlessly in CEL expressions
 * with full operator overload support.
 *
 * @api
 */
final readonly class DecimalNumber implements MessageInterface, ZeroValueInterface
{
    public function __construct(
        private Decimal $inner,
    ) {}

    /**
     * A decimal is a zero value when it is numerically zero, so that
     * `optional.ofNonZeroValue(decimal("0"))` yields `optional.none()`.
     */
    #[Override]
    public function isZeroValue(): bool
    {
        return $this->inner->isZero();
    }

    /**
     * Gets the wrapped Decimal\Decimal instance.
     */
    public function getInner(): Decimal
    {
        return $this->inner;
    }

    #[Override]
    public function toCelValue(): Value
    {
        return new MessageValue($this, []);
    }

    /**
     * @throws InternalException Always throws as DecimalNumber cannot be constructed from CEL fields.
     */
    #[Override]
    public static function fromCelFields(array $fields): static
    {
        // DecimalNumber is not constructible from CEL fields
        // It's created through value resolvers from Decimal\Decimal or the decimal() function
        throw InternalException::forMessage('DecimalNumber cannot be constructed from CEL fields');
    }
}
