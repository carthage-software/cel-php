<?php

declare(strict_types=1);

namespace Cel\Syntax\Member;

use Cel\Span\Span;
use Cel\Syntax\Expression;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\SelectorNode;
use Override;

/**
 * Represents a function call expression.
 *
 * Examples:
 *
 * ```cel
 * func()
 * target.func(arg1, arg2)
 * ```
 */
final readonly class CallExpression extends Expression
{
    /**
     * @param null|Expression                $target The call target.
     * @param null|Span                      $targetSeparator The span of the target separator `.`.
     * @param SelectorNode                   $function           The function or method being called.
     * @param Span                           $openingParenthesis The span of the opening parenthesis `(`.
     * @param PunctuatedSequence<Expression> $arguments          The list of argument expressions.
     * @param Span                           $closingParenthesis The span of the closing parenthesis `)`.
     */
    public function __construct(
        public null|Expression $target,
        public null|Span $targetSeparator,
        public SelectorNode $function,
        public Span $openingParenthesis,
        public PunctuatedSequence $arguments,
        public Span $closingParenthesis,
    ) {}

    #[Override]
    public function getKind(): ExpressionKind
    {
        return ExpressionKind::Call;
    }

    #[Override]
    public function getChildren(): array
    {
        return [$this->function, ...$this->arguments->elements];
    }

    #[Override]
    public function getSpan(): Span
    {
        if ($this->target !== null) {
            return $this->target->getSpan()->join($this->closingParenthesis);
        }

        if ($this->targetSeparator !== null) {
            return $this->targetSeparator->join($this->closingParenthesis);
        }

        return $this->function->getSpan()->join($this->closingParenthesis);
    }
}
