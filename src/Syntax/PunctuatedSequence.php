<?php

declare(strict_types=1);

namespace Cel\Syntax;

use ArrayIterator;
use Cel\Exception\NoSuchElementException;
use Cel\Span\Span;
use IteratorAggregate;
use Override;
use Traversable;

use function array_key_exists;
use function count;
use function sprintf;

/**
 * Represents a punctuated list of nodes, like arguments in a function call or elements in a list literal.
 * It preserves the spans of the punctuation (e.g., commas).
 *
 * @template-covariant T of Node
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
     * Returns the element at the given index.
     *
     * @param int $index The zero-based index of the element.
     *
     * @return T The element at the index.
     *
     * @throws NoSuchElementException If no element exists at the index.
     */
    public function at(int $index): Node
    {
        if (!array_key_exists($index, $this->elements)) {
            throw new NoSuchElementException(sprintf('No element exists at index %d.', $index));
        }

        return $this->elements[$index];
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

        $firstSpan = $this->at(0)->getSpan();
        $lastSpan = $this->at($this->count() - 1)->getSpan();

        $lastComma = null;
        foreach ($this->commas as $comma) {
            $lastComma = $comma;
        }

        if (null !== $lastComma && $lastComma->end > $lastSpan->end) {
            $lastSpan = $lastComma;
        }

        return $firstSpan->join($lastSpan);
    }
}
