<?php

declare(strict_types=1);

namespace Cel\Runtime\Exception;

/**
 * Thrown when an arithmetic operation on an unsigned integer results in a value
 * outside the valid range (i.e., less than zero).
 */
final class TypeConversionException extends RuntimeException
{
}
