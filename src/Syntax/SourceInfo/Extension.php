<?php

namespace Cel\Syntax\SourceInfo;

use Cel\Syntax\SourceInfo\Extension\Component;
use Cel\Syntax\SourceInfo\Extension\Version;
use JsonSerializable;
use Override;
use Psl\Vec;

use function Psl\Vec;

/**
 * An extension that was used during parsing or type-checking.
 */
final readonly class Extension implements JsonSerializable
{
    /**
     * The unique identifier of the extension.
     *
     * Example: `constant_folding`
     */
    public string $id;

    /**
     * The components that are affected by this extension.
     *
     * If a component is listed here, it must understand the extension for the
     * expression to evaluate correctly.
     *
     * @var list<Component>
     */
    public array $affected_components;

    /**
     * The version of the extension.
     *
     * This may be skipped if it is not meaningful for the extension.
     *
     * @var Version
     */
    public Version $version;

    /**
     * @param string          $id                  The unique identifier of the extension.
     * @param list<Component> $affected_components The components that are affected by this extension.
     * @param Version         $version             The version of the extension.
     */
    public function __construct(string $id, array $affected_components, Version $version)
    {
        $this->id = $id;
        $this->affected_components = $affected_components;
        $this->version = $version;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'affected_components' => Vec\map($this->affected_components, fn(Component $c) => $c->jsonSerialize()),
            'version' => $this->version->jsonSerialize(),
        ];
    }
}
