<?php

declare(strict_types=1);

namespace Cel\Parser\Exception;

use Cel\Token\TokenKind;
use RuntimeException;

use function array_map;
use function count;
use function implode;

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
        if (0 !== count($expected)) {
            $expectedNames = array_map(fn(TokenKind $k): string => $k->name, $expected);
            $message .= ', expected one of: `' . implode('`, `', $expectedNames) . '`';
        }

        parent::__construct($message . " at position {$position}.");
    }
}
