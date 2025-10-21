<?php

declare(strict_types=1);

namespace Cel\Value;

enum ValueKind: string
{
    case Boolean = 'bool';
    case Bytes = 'bytes';
    case Float = 'float';
    case Integer = 'int';
    case List = 'list';
    case Map = 'map';
    case Message = 'message';
    case Null = 'null';
    case String = 'string';
    case UnsignedInteger = 'uint';
    case Duration = 'duration';
    case Timestamp = 'timestamp';

    public function isAggregate(): bool
    {
        return match ($this) {
            self::List, self::Map, self::Message => true,
            default => false,
        };
    }
}
