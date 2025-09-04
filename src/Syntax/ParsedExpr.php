<?php

declare(strict_types=1);

namespace Cel\Syntax;

use JsonSerializable;
use Override;

/**
 * An expression with its associated source information.
 *
 * This class is the result of the parsing process, containing both the
 * abstract syntax tree (AST) of the expression and the source information
 * that maps the AST nodes back to the original source code.
 */
final readonly class ParsedExpr implements JsonSerializable
{
    /**
     * @param IdedExpr   $expr        The parsed expression.
     * @param SourceInfo $source_info The source information.
     */
    public function __construct(
        public IdedExpr $expr,
        public SourceInfo $source_info,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'expr' => $this->expr->jsonSerialize(),
            'source_info' => $this->source_info->jsonSerialize(),
        ];
    }
}
