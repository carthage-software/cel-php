<?php

declare(strict_types=1);

namespace Cel\Common;

/**
 * Defines the basic behavior for objects that manage a cursor over an input source.
 */
interface HasCursorInterface
{
    /**
     * Returns the current cursor position relative to the start of the input.
     *
     * @return int<0, max> The current cursor position.
     */
    public function cursorPosition(): int;

    /**
     * Checks if the cursor has reached or passed the end of the input.
     *
     * @return bool True if the current cursor position is greater than or equal to the input length.
     */
    public function hasReachedEnd(): bool;
}
