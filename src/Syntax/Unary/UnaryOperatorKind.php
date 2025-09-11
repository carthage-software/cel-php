<?php

declare(strict_types=1);

namespace Cel\Syntax\Unary;

enum UnaryOperatorKind
{
    case Negate;
    case Not;
}
