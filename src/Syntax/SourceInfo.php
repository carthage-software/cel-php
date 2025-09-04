<?php

declare(strict_types=1);

namespace Cel\Syntax;

use ArrayObject;
use Cel\Syntax\SourceInfo\Extension;
use JsonSerializable;
use Override;
use Psl\Dict;
use Psl\Vec;

/**
 * Source information collected at parse time.
 *
 * This class contains metadata about the source code that was parsed,
 * including the syntax version, location, line offsets, and a map of
 * expression IDs to their positions in the source.
 */
final readonly class SourceInfo implements JsonSerializable
{
    /**
     * The syntax version of the source, e.g., `cel1`.
     */
    public string $syntax_version;

    /**
     * The location name of the source.
     *
     * This can be a file name, a UI element, or any other identifier for the source.
     * All position information is relative to this location.
     *
     * Example: `acme/app/AnvilPolicy.cel`
     */
    public string $location;

    /**
     * A list of code point offsets where newlines `\n` appear.
     *
     * This list is monotonically increasing. The line number of a given position
     * can be calculated by finding the index `i` such that
     * `line_offsets[i] < id_positions[id] < line_offsets[i+1]`.
     * The column can be derived from `id_positions[id] - line_offsets[i]`.
     *
     * @var list<int>
     */
    public array $line_offsets;

    /**
     * A map from the expression node ID to its code point offset within the source.
     *
     * @var array<int, int>
     */
    public array $positions;

    /**
     * A list of extensions that were used while parsing or type-checking the source.
     *
     * @var list<Extension>
     */
    public array $extensions;

    /**
     * @param string $syntax_version The syntax version of the source.
     * @param string $location The location name of the source.
     * @param list<int> $line_offsets A list of code point offsets where newlines `\n` appear.
     * @param array<int, int> $positions A map from the expression node ID to its code point offset within the source.
     * @param list<Extension> $extensions A list of extensions that were used while parsing or type-checking the source.
     */
    public function __construct(
        string $syntax_version,
        string $location,
        array $line_offsets,
        array $positions,
        array $extensions,
    ) {
        $this->syntax_version = $syntax_version;
        $this->location = $location;
        $this->line_offsets = $line_offsets;
        $this->positions = $positions;
        $this->extensions = $extensions;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'syntax_version' => $this->syntax_version,
            'location' => $this->location,
            'line_offsets' => $this->line_offsets,
            'positions' => new ArrayObject($this->positions),
            'extensions' => Vec\map($this->extensions, fn(Extension $e) => $e->jsonSerialize()),
        ];
    }
}
