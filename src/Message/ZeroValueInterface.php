<?php

declare(strict_types=1);

namespace Cel\Message;

/**
 * Implemented by messages that can report whether they represent a "zero value".
 *
 * CEL's `optional.ofNonZeroValue` treats zero values as absent. A message value
 * is never considered a zero value unless its underlying message implements this
 * interface and reports itself as zero (mirroring cel-go's `Zeroer` trait for
 * proto messages).
 */
interface ZeroValueInterface
{
    /**
     * Indicates whether the message represents a zero (default/empty) value.
     */
    public function isZeroValue(): bool;
}
