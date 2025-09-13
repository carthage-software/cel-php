<?php

declare(strict_types=1);

namespace Cel\Tests\Fixture;

use Cel\Runtime\Exception\InvalidMessageFieldsException;
use Cel\Runtime\Message\MessageInterface;
use Cel\Runtime\Value\MessageValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Override;
use Psl\Str;
use Psl\Type;

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
        try {
            $fields = Type\shape([
                'name' => Type\instance_of(StringValue::class),
                'email' => Type\instance_of(StringValue::class),
            ])->assert($fields);
        } catch (Type\Exception\ExceptionInterface) {
            throw new InvalidMessageFieldsException(Str\format(
                'Invalid fields for `UserMessage`, expected `name` and `email` of type `string`',
            ));
        }

        return new static($fields['name']->value, $fields['email']->value);
    }
}
