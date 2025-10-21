<?php

declare(strict_types=1);

namespace Cel\Parser\Exception;

use Cel\Token\Token;
use Cel\Token\TokenKind;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;
use RuntimeException;

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
        if (0 !== Iter\count($this->expected)) {
            $expectedNames = Vec\map($this->expected, static fn(TokenKind $k): string => $k->name);

            $message .= ', expected one of: `' . Str\join($expectedNames, '`, `') . '`';
        }

        parent::__construct($message . " at span [{$this->found->span->start}, {$this->found->span->end}].");
    }
}
