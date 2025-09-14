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

final readonly class CommentMessage implements MessageInterface
{
    public function __construct(
        public string $content,
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function toCelValue(): Value
    {
        return new MessageValue($this, [
            'content' => new StringValue($this->content),
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
                'content' => Type\instance_of(StringValue::class),
            ])->assert($fields);
        } catch (Type\Exception\ExceptionInterface $e) {
            throw new InvalidMessageFieldsException(Str\format(
                'Invalid fields for `CommentMessage`: %s',
                $e->getMessage(),
            ));
        }

        return new static($fields['content']->value);
    }
}
