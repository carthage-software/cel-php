<?php

declare(strict_types=1);

namespace Cel\Tests\Fixture;

use Cel\Exception\InvalidMessageFieldsException;
use Cel\Message\MessageInterface;
use Cel\Value\MessageValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

/**
 * A message with a required `name` field and an optional `nickname` field.
 *
 * Used to exercise optional field selection and optional field initialization
 * (`Msg{?field: ...}`) against message values, including the case where an
 * optional field is genuinely absent.
 */
final readonly class ProfileMessage implements MessageInterface
{
    public function __construct(
        public string $name,
        public null|string $nickname = null,
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function toCelValue(): Value
    {
        $fields = ['name' => new StringValue($this->name)];
        if (null !== $this->nickname) {
            $fields['nickname'] = new StringValue($this->nickname);
        }

        return new MessageValue($this, $fields);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function fromCelFields(array $fields): static
    {
        $name = $fields['name'] ?? null;
        if (!$name instanceof StringValue) {
            throw new InvalidMessageFieldsException(
                'Invalid fields for `ProfileMessage`: `name` is required and must be a string',
            );
        }

        $nickname = $fields['nickname'] ?? null;
        if (null !== $nickname && !$nickname instanceof StringValue) {
            throw new InvalidMessageFieldsException('Invalid fields for `ProfileMessage`: `nickname` must be a string');
        }

        return new static($name->value, $nickname?->value);
    }
}
