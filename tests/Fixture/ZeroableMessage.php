<?php

declare(strict_types=1);

namespace Cel\Tests\Fixture;

use Cel\Exception\InternalException;
use Cel\Message\MessageInterface;
use Cel\Message\ZeroValueInterface;
use Cel\Value\MessageValue;
use Cel\Value\Value;
use Override;

/**
 * A message that reports a configurable zero-value state, used to exercise
 * {@see MessageValue::isZeroValue()} without depending on ext-decimal.
 */
final readonly class ZeroableMessage implements MessageInterface, ZeroValueInterface
{
    public function __construct(
        public bool $zero,
    ) {}

    #[Override]
    public function isZeroValue(): bool
    {
        return $this->zero;
    }

    #[Override]
    public function toCelValue(): Value
    {
        return new MessageValue($this, []);
    }

    /**
     * @throws InternalException Always: this fixture is not constructible from CEL fields.
     */
    #[Override]
    public static function fromCelFields(array $fields): static
    {
        throw InternalException::forMessage('ZeroableMessage cannot be constructed from CEL fields');
    }
}
