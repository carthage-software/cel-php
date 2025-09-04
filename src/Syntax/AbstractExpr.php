<?php

declare(strict_types=1);

namespace Cel\Syntax;

use JsonSerializable;
use Override;

/**
 * Base class for all expression nodes in the AST.
 *
 * @inheritors CallExpr|ComprehensionExpr|IdentExpr|ListExpr|ConstantLiteralExpr|MapExpr|SelectExpr|StructExpr
 */
abstract readonly class AbstractExpr implements JsonSerializable
{
    #[Override]
    abstract public function jsonSerialize(): array;
}
