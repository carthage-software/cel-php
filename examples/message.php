<?php

declare(strict_types=1);

namespace Cel\Examples;

use Cel;
use Cel\Exception\InvalidMessageFieldsException;
use Cel\Value;
use Override;
use Psl\IO;
use Psl\Json;
use RuntimeException;

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
        $id = $fields['id'] ?? null;
        $name = $fields['name'] ?? null;
        $email = $fields['email'] ?? null;

        if (
            !$id instanceof Value\IntegerValue
            || !$name instanceof Value\StringValue
            || null !== $email && !$email instanceof Value\StringValue && !$email instanceof Value\NullValue
        ) {
            throw new InvalidMessageFieldsException('Unable to create RequestMessage from CEL fields.');
        }

        return new self(
            id: $id->getRawValue(),
            name: $name->getRawValue(),
            email: $email instanceof Value\StringValue ? $email->getRawValue() : null,
        );
    }
}

$configuration = new Cel\Runtime\Configuration(allowedMessageClasses: [
    RequestMessage::class,
]);

$message = Cel\evaluate('Cel.Examples.RequestMessage { id: 1234, name: "John Doe" }', configuration: $configuration);
if (!$message->getRawValue() instanceof RequestMessage) {
    throw new RuntimeException('Expected a RequestMessage instance.');
}

$message = Cel\evaluate(
    'Cel.Examples.RequestMessage { id: 1234, name: "John Doe", email: null }',
    configuration: $configuration,
);
if (!$message->getRawValue() instanceof RequestMessage) {
    throw new RuntimeException('Expected a RequestMessage instance.');
}

$message = Cel\evaluate(
    'Cel.Examples.RequestMessage { id: 1234, name: "John Doe", email: "john.doe@example.com" }',
    configuration: $configuration,
);
if (!$message->getRawValue() instanceof RequestMessage) {
    throw new RuntimeException('Expected a RequestMessage instance.');
}

$result = Cel\evaluate(
    'has(message.email) ? message.email : null',
    variables: ['message' => $message],
    configuration: $configuration,
);
if ('john.doe@example.com' !== $result->getRawValue()) {
    throw new RuntimeException('Expected email to exist');
}

IO\write_line('RequestMessage created successfully: %s', Json\encode($message->getRawValue(), true));
