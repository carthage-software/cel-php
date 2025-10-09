<?php

declare(strict_types=1);

namespace Cel\Syntax\Literal;

use Cel\Syntax\Expression;

/**
 * @template T
 *
 * @inheritors BoolLiteralExpression|BytesLiteralExpression|FloatLiteralExpression|IntegerLiteralExpression|NullLiteralExpression|StringLiteralExpression|UnsignedIntegerLiteralExpression
 */
abstract readonly class LiteralExpression extends Expression
{
    /**
     * @return T
     */
    abstract public function getValue(): mixed;

    abstract public function getRaw(): string;
}
