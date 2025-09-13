<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Strings\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\BytesValue;
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
final readonly class ToAsciiLowerFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'toAsciiLower';
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
        yield [ValueKind::String] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): StringValue {
                /** @var StringValue $target */
                $target = $arguments[0];

                $result = '';
                foreach (Str\chunk($target->value) as $char) {
                    $ord = Str\ord($char);
                    // A = 65, Z = 90
                    $result .= $ord >= 65 && $ord <= 90 ? Str\lowercase($char) : $char;
                }

                return new StringValue($result);
            };

        yield [ValueKind::Bytes] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): BytesValue {
                /** @var BytesValue $target */
                $target = $arguments[0];

                $result = '';
                for ($i = 0; $i < Byte\length($target->value); ++$i) {
                    $byte = $target->value[$i];
                    $ord = Byte\ord($byte);
                    // A = 65, Z = 90
                    $result .= $ord >= 65 && $ord <= 90 ? Byte\chr($ord + 32) : $byte;
                }

                return new BytesValue($result);
            };
    }
}
