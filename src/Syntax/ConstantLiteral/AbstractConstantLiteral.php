<?php

declare(strict_types=1);

namespace Cel\Syntax\ConstantLiteral;

use JsonSerializable;

/**
 * Base class for all literal values.
 *
 * @inheritors BoolConstantLiteral|BytesConstantLiteral|DoubleConstantLiteral|IntConstantLiteral|NullConstantLiteral|StringConstantLiteral|UintConstantLiteral
 */
abstract readonly class AbstractConstantLiteral implements JsonSerializable
{
}
