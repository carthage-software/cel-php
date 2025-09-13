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
final readonly class TypeOfFunction implements FunctionInterface
{
    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return 'typeOf';
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
        foreach (ValueKind::cases() as $kind) {
            yield [$kind] =>
                /**
                 * @param CallExpression $call      The call expression representing the function call.
                 * @param list<Value>    $arguments The arguments passed to the function.
                 */
                static function (CallExpression $call, array $arguments): StringValue {
                    $value = $arguments[0];

                    return new StringValue($value->getKind()->value);
                };
        }
    }
}
