<?php

declare(strict_types=1);

namespace Cel\Token;

/**
 * Defines the associativity of an operator.
 *
 * Operator associativity determines how operators of the same precedence are grouped in the absence of parentheses.
 */
enum Associativity
{
    case LeftToRight;
    case RightToLeft;
}
