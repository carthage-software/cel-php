<?php

declare(strict_types=1);

namespace Cel\Value;

/**
 * Constructs the `google.protobuf` well-known types that map directly onto
 * native CEL values, without a protobuf wire runtime.
 *
 * The scalar wrapper types unwrap to their underlying primitive (an empty
 * literal yields that primitive's zero value, and a constructed wrapper is
 * never null), and `google.protobuf.Value` yields null. Types that genuinely
 * need a proto runtime (`Any`, `Struct`, `ListValue`, message schemas) are not
 * handled here and fall through to the ordinary message path.
 */
final readonly class WellKnownType
{
    private function __construct() {}

    /**
     * Returns the field names a well-known type may be constructed with, or null
     * when the type is not a natively representable well-known type. The scalar
     * wrappers accept a single `value` field; `Value` accepts none (only its
     * empty form is supported).
     *
     * @return null|list<string>
     */
    public static function allowedFields(string $typename): null|array
    {
        return match ($typename) {
            'google.protobuf.BoolValue',
            'google.protobuf.Int32Value',
            'google.protobuf.Int64Value',
            'google.protobuf.UInt32Value',
            'google.protobuf.UInt64Value',
            'google.protobuf.FloatValue',
            'google.protobuf.DoubleValue',
            'google.protobuf.StringValue',
            'google.protobuf.BytesValue',
                => ['value'],
            'google.protobuf.Value' => [],
            default => null,
        };
    }

    /**
     * Constructs the CEL value denoted by a well-known type message literal, or
     * null when the type is not a natively representable well-known type. Callers
     * validate the fields against {@see self::allowedFields()} first.
     *
     * @param array<string, Value> $fields The evaluated message initializers.
     */
    public static function construct(string $typename, array $fields): null|Value
    {
        $value = $fields['value'] ?? null;

        return match ($typename) {
            'google.protobuf.BoolValue' => $value ?? new BooleanValue(false),
            'google.protobuf.Int32Value', 'google.protobuf.Int64Value' => $value ?? new IntegerValue(0),
            'google.protobuf.UInt32Value', 'google.protobuf.UInt64Value' => $value ?? new UnsignedIntegerValue(0),
            'google.protobuf.FloatValue', 'google.protobuf.DoubleValue' => $value ?? new FloatValue(0.0),
            'google.protobuf.StringValue' => $value ?? new StringValue(''),
            'google.protobuf.BytesValue' => $value ?? new BytesValue(''),
            'google.protobuf.Value' => new NullValue(),
            default => null,
        };
    }
}
