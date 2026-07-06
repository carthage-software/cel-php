<?php

declare(strict_types=1);

namespace Cel\Parser\Exception;

use Cel\Token\Token;
use Cel\Token\TokenKind;
use RuntimeException;

use function array_map;
use function count;
use function implode;

final class UnexpectedTokenException extends RuntimeException implements ExceptionInterface
{
    /**
     * @param Token $found
     * @param list<TokenKind> $expected
     */
    public function __construct(
        public readonly Token $found,
        public readonly array $expected = [],
    ) {
        $message = "Unexpected token `{$this->found->kind->name}` with value '{$this->found->value}'";
        if (0 !== count($this->expected)) {
            $expectedNames = array_map(static fn(TokenKind $k): string => $k->name, $this->expected);

            $message .= ', expected one of: `' . implode('`, `', $expectedNames) . '`';
        }

        parent::__construct($message . " at span [{$this->found->span->start}, {$this->found->span->end}].");
    }
}
