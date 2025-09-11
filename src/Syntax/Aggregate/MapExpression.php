<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\PunctuatedSequence;
use Override;

/**
 * Represents a map literal, e.g., `{"key1": "value1", "key2": 123}`.
 */
final readonly class MapExpression extends Expression
{
    /**
     * @param Span $openingBrace The span of the opening brace `{`.
     * @param PunctuatedSequence<MapEntryNode> $entries The list of key-value entries.
     * @param Span $closingBrace The span of the closing brace `}`.
     */
    public function __construct(
        public Span $openingBrace,
        public PunctuatedSequence $entries,
        public Span $closingBrace,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Map;
    }

    #[Override]
    public function getChildren(): array
    {
        return $this->entries->elements;
    }

    #[Override]
    public function getSpan(): Span
    {
        return $this->openingBrace->join($this->closingBrace);
    }
}
