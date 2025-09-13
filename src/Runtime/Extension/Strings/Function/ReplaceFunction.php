<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Strings\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BytesValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\StringValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Str;
use Psl\Str\Byte;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class ReplaceFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'replace';
    }

    /**
     * @return iterable<list<ValueKind>, (callable(CallExpression, list<Value>): Value)>
     */
    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::String, ValueKind::String, ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var StringValue $haystack */
                $haystack = $arguments[0];
                /** @var StringValue $needle */
                $needle = $arguments[1];
                /** @var StringValue $replacement */
                $replacement = $arguments[2];

                if ($needle->value === '') {
                    // If the needle is an empty string, we insert the replacement between every character.
                    $result = Str\join(Str\chunk($haystack->value), $replacement->value) . $replacement->value;
                    if ($haystack->value !== '') {
                        $result = $replacement->value . $result;
                    }

                    return new StringValue($result);
                }

                return new StringValue(Str\replace($haystack->value, $needle->value, $replacement->value));
            };

        yield [ValueKind::Bytes, ValueKind::Bytes, ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BytesValue {
                /** @var BytesValue $haystack */
                $haystack = $arguments[0];
                /** @var BytesValue $needle */
                $needle = $arguments[1];
                /** @var BytesValue $replacement */
                $replacement = $arguments[2];

                return new BytesValue(Byte\replace($haystack->value, $needle->value, $replacement->value));
            };
    }
}
