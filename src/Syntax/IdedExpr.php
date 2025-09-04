<?php

declare(strict_types=1);

namespace Cel\Syntax;

use JsonSerializable;
use Override;

/**
 * An expression node that has a unique ID.
 */
final readonly class IdedExpr implements JsonSerializable
{
    /**
     * @param int<0, max>  $id   A unique ID for this expression node. This ID is assigned by the parser and is used to
     *                           associate type information and other attributes with the node.
     * @param AbstractExpr $expr The expression node.
     */
    public function __construct(
        public int $id,
        public AbstractExpr $expr,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return (
            [
                'id' => $this->id,
            ] + $this->expr->jsonSerialize()
        );
    }
}
