<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Core\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class BytesFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'bytes';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    /**
     * @return iterable<list<ValueKind>, (callable(CallExpression, list<Value>): Value)>
     */
    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BytesValue {
                /** @var BytesValue $value */
                $value = $arguments[0];

                return new BytesValue($value->value);
            };

        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BytesValue {
                /** @var StringValue $value */
                $value = $arguments[0];

                // Per CEL spec, converting a string to bytes uses its UTF-8 representation.
                // Since PHP strings are byte arrays, this is a direct conversion.
                return new BytesValue($value->value);
            };
    }
}
