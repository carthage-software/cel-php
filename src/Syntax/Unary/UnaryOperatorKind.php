<?php

declare(strict_types=1);

namespace Cel\Syntax\Unary;

enum UnaryOperatorKind
{
    case Negate;
    case Not;

    public function getSymbol(): string
    {
        return match ($this) {
            self::Negate => '-',
            self::Not => '!',
        };
    }
}
