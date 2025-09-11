<?php

declare(strict_types=1);

namespace Cel\Syntax;

enum ExpressionKind
{
    case Literal;
    case Conditional;
    case Binary;
    case Unary;
    case Parenthesized;

    // Literals
    case StringLiteral;
    case BytesLiteral;
    case FloatLiteral;
    case IntLiteral;
    case UIntLiteral;
    case BoolLiteral;
    case NullLiteral;

    // Member and Call
    case Identifier;
    case MemberAccess;
    case Index;
    case Call;

    // Aggregates
    case List;
    case Map;
    case Message;
}
