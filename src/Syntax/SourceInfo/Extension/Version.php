<?php

declare(strict_types=1);

namespace Cel\Syntax\SourceInfo\Extension;

use JsonSerializable;
use Override;

/**
 * Represents the version of an extension.
 */
final readonly class Version implements JsonSerializable
{
    /**
     * The major version number.
     *
     * Major version changes indicate different required support levels from the
     * components.
     *
     * @var int<0, max>
     */
    public int $major;

    /**
     * The minor version number.
     *
     * Minor version changes must not change the observed behavior from existing
     * implementations, but may be provided for informational purposes.
     *
     * @var int<0, max>
     */
    public int $minor;

    /**
     * @param int<0, max> $major The major version number.
     * @param int<0, max> $minor The minor version number.
     */
    public function __construct(int $major, int $minor)
    {
        $this->major = $major;
        $this->minor = $minor;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'major' => $this->major,
            'minor' => $this->minor,
        ];
    }
}
