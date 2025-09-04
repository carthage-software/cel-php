<?php

declare(strict_types=1);

namespace Cel\Syntax\SourceInfo\Extension;

use JsonSerializable;
use Override;

/**
 * Specifies a CEL component.
 */
enum Component: int implements JsonSerializable
{
    /**
     * Unspecified component.
     */
    case Unspecified = 0;

    /**
     * The parser component, which converts a CEL string to an AST.
     */
    case Parser = 1;

    /**
     * The type-checker component, which checks that references in an AST are
     * defined and that types agree.
     */
    case TypeChecker = 2;

    /**
     * The runtime component, which evaluates a parsed and optionally
     * checked CEL AST against a context.
     */
    case Runtime = 3;

    #[Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
