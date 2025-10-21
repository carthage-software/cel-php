<?php

declare(strict_types=1);

namespace Cel\Syntax\Aggregate;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\SelectorNode;
use Override;

/**
 * Represents a message construction, e.g., `google.example.v1.MyMessage{field: "value"}`.
 */
final readonly class MessageExpression extends Expression
{
    /**
     * @param Span|null                                $dot The span of the leading dot `.`, if present.
     * @param SelectorNode                             $selector The first selector in the message type path.
     * @param PunctuatedSequence<SelectorNode>         $followingSelectors The subsequent selectors in the message type path.
     * @param Span                                     $openingBrace The span of the opening brace `{`.
     * @param PunctuatedSequence<FieldInitializerNode> $initializers The list of field initializers.
     * @param Span                                     $closingBrace The span of the closing brace `}`.
     */
    public function __construct(
        public null|Span $dot,
        public SelectorNode $selector,
        public PunctuatedSequence $followingSelectors,
        public Span $openingBrace,
        public PunctuatedSequence $initializers,
        public Span $closingBrace,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Message;
    }

    #[Override]
    public function getChildren(): array
    {
        return [$this->selector, ...$this->followingSelectors->elements, ...$this->initializers->elements];
    }

    #[Override]
    public function getSpan(): Span
    {
        $firstSpan = $this->dot ?? $this->selector->getSpan();

        return $firstSpan->join($this->closingBrace);
    }
}
