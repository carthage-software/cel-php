<?php

declare(strict_types=1);

namespace Cel\Value;

use Cel\Exception\UnsupportedOperationException;
use Cel\Message\MessageInterface;
use Cel\Message\ZeroValueInterface;
use Override;

/**
 * Represents a message value.
 *
 * @api
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

    /**
     * A message is a zero value only when its underlying message opts in via
     * {@see ZeroValueInterface} and reports itself as zero. Messages that do not
     * implement it are never treated as zero values.
     */
    #[Override]
    public function isZeroValue(): bool
    {
        return $this->message instanceof ZeroValueInterface && $this->message->isZeroValue();
    }

    #[Override]
    public function isEqual(Value $other): bool
    {
        if (!$other instanceof MessageValue) {
            return false;
        }

        if ($this->message::class !== $other->message::class) {
            return false;
        }

        foreach ($this->fields as $field => $value) {
            $otherValue = $other->getField($field);

            if (null === $otherValue || !$value->isEqual($otherValue)) {
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
    public function getRawValue(): MessageInterface
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
