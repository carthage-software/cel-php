<?php

declare(strict_types=1);

namespace Cel\Examples;

use Cel;
use Cel\Exception\InvalidMessageFieldsException;
use Cel\Value;
use Override;
use Psl;
use Psl\Json;
use Psl\Type;

require_once __DIR__ . '/../vendor/autoload.php';

final readonly class RequestMessage implements Cel\Message\MessageInterface
{
    public function __construct(
        public int $id,
        public string $name,
        public null|string $email,
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function toCelValue(): Value\Value
    {
        return new Value\MessageValue($this, [
            'id' => Value\Value::from($this->id),
            'name' => Value\Value::from($this->name),
            'email' => Value\Value::from($this->email),
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
                'id' => Type\instance_of(Value\IntegerValue::class),
                'name' => Type\instance_of(Value\StringValue::class),
                'email' => Type\optional(Type\union(
                    Type\instance_of(Value\NullValue::class),
                    Type\instance_of(Value\StringValue::class),
                )),
            ])->assert($fields);

            $id = $fields['id']->getRawValue();
            $name = $fields['name']->getRawValue();
            $email = isset($fields['email']) ? $fields['email']->getRawValue() : null;

            return new self(id: $id, name: $name, email: $email);
        } catch (Type\Exception\AssertException $e) {
            throw new InvalidMessageFieldsException('Unable to create RequestMessage from CEL fields.', previous: $e);
        }
    }
}

$configuration = new Cel\Runtime\Configuration(allowedMessageClasses: [
    RequestMessage::class,
]);

$message = Cel\evaluate('Cel.Examples.RequestMessage { id: 1234, name: "John Doe" }', configuration: $configuration);
Psl\invariant($message->getRawValue() instanceof RequestMessage, 'Expected a RequestMessage instance.');

$message = Cel\evaluate(
    'Cel.Examples.RequestMessage { id: 1234, name: "John Doe", email: null }',
    configuration: $configuration,
);
Psl\invariant($message->getRawValue() instanceof RequestMessage, 'Expected a RequestMessage instance.');

$message = Cel\evaluate(
    'Cel.Examples.RequestMessage { id: 1234, name: "John Doe", email: "john.doe@example.com" }',
    configuration: $configuration,
);
Psl\invariant($message->getRawValue() instanceof RequestMessage, 'Expected a RequestMessage instance.');

$result = Cel\evaluate(
    'has(message.email) ? message.email : null',
    variables: ['message' => $message],
    configuration: $configuration,
);
Psl\invariant($result->getRawValue() === 'john.doe@example.com', 'Expected email to exist');

Psl\IO\write_line('RequestMessage created successfully: %s', Json\encode($message->getRawValue(), true));
