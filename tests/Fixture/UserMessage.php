<?php

declare(strict_types=1);

namespace Cel\Tests\Fixture;

use Cel\Exception\InvalidMessageFieldsException;
use Cel\Message\MessageInterface;
use Cel\Value\MessageValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use function count;

final readonly class UserMessage implements MessageInterface
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function toCelValue(): Value
    {
        return new MessageValue($this, [
            'name' => new StringValue($this->name),
            'email' => new StringValue($this->email),
        ]);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function fromCelFields(array $fields): static
    {
        $name = $fields['name'] ?? null;
        $email = $fields['email'] ?? null;
        if (!$name instanceof StringValue || !$email instanceof StringValue || 2 !== count($fields)) {
            throw new InvalidMessageFieldsException(
                'Invalid fields for `UserMessage`, expected `name` and `email` of type `string`',
            );
        }

        return new static($name->value, $email->value);
    }
}
