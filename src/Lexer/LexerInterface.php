<?php

declare(strict_types=1);

namespace Cel\Lexer;

use Cel\Common\HasCursorInterface;
use Cel\Input\InputInterface;
use Cel\Token\Token;

/**
 * Defines the contract for a CEL lexer, which breaks an input stream into a series of tokens.
 */
interface LexerInterface extends HasCursorInterface
{
    /**
     * Returns the underlying input stream being processed by the lexer.
     */
    public function getInput(): InputInterface;

    /**
     * Reads the next token from the input stream and advances the cursor.
     *
     * @return null|Token Returns the next token, or null if the end of the input has been reached.
     */
    public function advance(): null|Token;
}
