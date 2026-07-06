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
        $content = $fields['content'] ?? null;
        if (!$content instanceof StringValue || 1 !== count($fields)) {
            throw new InvalidMessageFieldsException(
                'Invalid fields for `CommentMessage`: expected field `content` of type `string`',
            );
        }

        return new static($content->value);
    }
}
