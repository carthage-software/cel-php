<?php

declare(strict_types=1);

namespace Cel\Runtime\Value;

use Cel\Runtime\Exception\UnsupportedOperationException;
use Cel\Runtime\Message\MessageInterface;
use Override;

/**
 * Represents a message value.
 */
final readonly class MessageValue extends Value
{
    /**
     * @param array<string, Value> $fields
     */
    public function __construct(
        public MessageInterface $message,
        public array $fields,
    ) {}

    #[Override]
    public function getKind(): ValueKind
    {
        return ValueKind::Message;
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof MessageValue) {
            throw UnsupportedOperationException::forEquality($this, $other);
        }

        if ($this->message::class !== $other->message::class) {
            return false;
        }

        foreach ($this->fields as $field => $value) {
            $otherValue = $other->getField($field);

            if ($otherValue === null || !$value->isEqual($otherValue)) {
                return false;
            }
        }

        return true;
    }

    #[Override]
    public function isLessThan(Value $other): bool
    {
        throw UnsupportedOperationException::forComparison($this, $other);
    }

    #[Override]
    public function isGreaterThan(Value $other): bool
    {
        throw UnsupportedOperationException::forComparison($this, $other);
    }

    #[Override]
    public function getNativeValue(): MessageInterface
    {
        return $this->message;
    }

    /**
     * Checks if a field exists.
     */
    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    /**
     * Retrieves a field by name.
     */
    public function getField(string $name): null|Value
    {
        return $this->fields[$name] ?? null;
    }
}
