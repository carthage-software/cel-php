<?php

declare(strict_types=1);

namespace Cel\Syntax\Literal;

use Cel\Syntax\Expression;

/**
 * @template T
 */
abstract readonly class LiteralExpression extends Expression
{
    /**
     * @return T
     */
    abstract public function getValue(): mixed;

    abstract public function getRaw(): string;
}
