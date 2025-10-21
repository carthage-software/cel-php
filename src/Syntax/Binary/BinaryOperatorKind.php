<?php

declare(strict_types=1);

namespace Cel\Syntax\Binary;

enum BinaryOperatorKind
{
    case LessThan;
    case LessThanOrEqual;
    case GreaterThan;
    case GreaterThanOrEqual;
    case Equal;
    case NotEqual;
    case In;
    case Plus;
    case Minus;
    case Multiply;
    case Divide;
    case Modulo;
    case And;
    case Or;

    public function isLogical(): bool
    {
        return match ($this) {
            self::And, self::Or => true,
            default => false,
        };
    }

    public function isComparison(): bool
    {
        return match ($this) {
            self::LessThan,
            self::LessThanOrEqual,
            self::GreaterThan,
            self::GreaterThanOrEqual,
            self::Equal,
            self::NotEqual,
            self::In,
                => true,
            default => false,
        };
    }

    public function isArithmetic(): bool
    {
        return match ($this) {
            self::Plus, self::Minus, self::Multiply, self::Divide, self::Modulo => true,
            default => false,
        };
    }

    public function isAdditive(): bool
    {
        return match ($this) {
            self::Plus, self::Minus => true,
            default => false,
        };
    }

    public function isMultiplicative(): bool
    {
        return match ($this) {
            self::Multiply, self::Divide, self::Modulo => true,
            default => false,
        };
    }

    public function getSymbol(): string
    {
        return match ($this) {
            self::LessThan => '<',
            self::LessThanOrEqual => '<=',
            self::GreaterThan => '>',
            self::GreaterThanOrEqual => '>=',
            self::Equal => '==',
            self::NotEqual => '!=',
            self::In => 'in',
            self::Plus => '+',
            self::Minus => '-',
            self::Multiply => '*',
            self::Divide => '/',
            self::Modulo => '%',
            self::And => '&&',
            self::Or => '||',
        };
    }
}
