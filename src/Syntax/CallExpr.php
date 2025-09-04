<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Override;
use Psl\Vec;

/**
 * Represents a function call expression.
 */
final readonly class CallExpr extends AbstractExpr
{
    /**
     * @param IdedExpr|null $target The target of the function call, if any. e.g., `target.function(args)`
     * @param string $function The name of the function being called.
     * @param list<IdedExpr> $args The arguments to the function.
     */
    public function __construct(
        public null|IdedExpr $target,
        public string $function,
        public array $args,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'call_expr' => [
                'target' => $this->target?->jsonSerialize(),
                'function' => $this->function,
                'args' => Vec\map($this->args, static fn(IdedExpr $expr): array => $expr->jsonSerialize()),
            ],
        ];
    }
}
