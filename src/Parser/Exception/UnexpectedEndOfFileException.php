<?php

declare(strict_types=1);

namespace Cel\Parser\Exception;

use Cel\Token\TokenKind;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;
use RuntimeException;

final class UnexpectedEndOfFileException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param list<TokenKind> $expected
     */
    public function __construct(
        public readonly int $position,
        public readonly array $expected = [],
    ) {
        $message = 'Unexpected end of file';
        if (0 !== Iter\count($this->expected)) {
            $expected_names = Vec\map($this->expected, static fn(TokenKind $k): string => $k->name);
            $message .= ', expected one of: `' . Str\join($expected_names, '`, `') . '`';
        }

        parent::__construct($message . " at position {$position}.");
    }
}
