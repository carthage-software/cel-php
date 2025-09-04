<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Cel\Span\Span;
use JsonSerializable;
use Override;

/**
 * The base class for all nodes in the Abstract Syntax Tree (AST).
 */
abstract readonly class AbstractNode implements JsonSerializable
{
    /**
     * Gets the location of the node in the original source code.
     */
    abstract public function getSpan(): Span;

    /**
     * A convenience method to get the starting offset of the node's span.
     */
    final public function getStartOffset(): int
    {
        return $this->getSpan()->start;
    }

    /**
     * A convenience method to get the ending offset of the node's span.
     */
    final public function getEndOffset(): int
    {
        return $this->getSpan()->end;
    }

    /**
     * Serializes the node to a JSON-compatible array.
     *
     * This method must be implemented by all subclasses to provide
     * the appropriate serialization logic for the specific node type.
     *
     * @return array{span: list{int, int}, ...} The JSON-compatible representation of the node.
     */
    #[Override]
    abstract public function jsonSerialize(): array;
}
