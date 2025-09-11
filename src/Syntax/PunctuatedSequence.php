<?php

declare(strict_types=1);

namespace Cel\Syntax;

use ArrayIterator;
use Cel\Span\Span;
use IteratorAggregate;
use Override;
use Traversable;

use function array_key_last;
use function count;

/**
 * Represents a punctuated list of nodes, like arguments in a function call or elements in a list literal.
 * It preserves the spans of the punctuation (e.g., commas).
 *
 * @template T of Node
 *
 * @implements IteratorAggregate<T>
 */
final readonly class PunctuatedSequence implements IteratorAggregate
{
    /**
     * @param list<T> $elements The nodes in the list.
     * @param list<Span> $commas The spans of the commas separating the nodes.
     */
    public function __construct(
        public array $elements,
        public array $commas,
    ) {}

    /**
     * Checks if the list has a trailing comma.
     * e.g., `[1, 2, 3,]`
     */
    public function hasTrailingComma(): bool
    {
        if ([] === $this->elements) {
            return false;
        }

        // If there are as many commas as elements, there's a trailing one.
        return count($this->commas) === count($this->elements);
    }

    /**
     * Returns the number of elements in the list.
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @return Traversable<T>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Calculates the combined span of all elements and commas in the list.
     * Returns null if the list is empty.
     */
    public function getSpan(): null|Span
    {
        if ([] === $this->elements) {
            return null;
        }

        $firstSpan = $this->elements[0]->getSpan();
        $lastSpan = $this->elements[array_key_last($this->elements)]->getSpan();

        if ([] !== $this->commas) {
            $lastKey = array_key_last($this->commas);
            $lastComma = $this->commas[$lastKey];
            if ($lastComma->end > $lastSpan->end) {
                $lastSpan = $lastComma;
            }
        }

        return $firstSpan->join($lastSpan);
    }
}
