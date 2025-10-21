<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Span\Span;

/**
 * The base class for all nodes in the Abstract Syntax Tree (AST).
 */
abstract readonly class Node
{
    /**
     * Gets the child nodes of this AST node.
     *
     * @return list<Node> An array of child nodes.
     */
    abstract public function getChildren(): array;

    /**
     * Gets the location of the node in the original source code.
     */
    abstract public function getSpan(): Span;
}
