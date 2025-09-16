<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\Math\Function;

use Cel\Runtime\Exception\EvaluationException;
use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\FloatValue;
use Cel\Runtime\Value\IntegerValue;
use Cel\Runtime\Value\ListValue;
use Cel\Runtime\Value\Value;
use Cel\Runtime\Value\ValueKind;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\Math;
use Psl\Str;

final readonly class MedianFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'median';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function isIdempotent(): bool
    {
        return true;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [ValueKind::List] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static function (CallExpression $call, array $arguments): FloatValue {
                /** @var ListValue $list */
                $list = $arguments[0];

                /** @var list<int|float> $numbers */
                $numbers = [];
                foreach ($list->value as $item) {
                    if (!$item instanceof IntegerValue && !$item instanceof FloatValue) {
                        throw new EvaluationException(
                            Str\format(
                                'median() only supports lists of integers and floats, got `%s`',
                                $item->getType(),
                            ),
                            $call->getSpan(),
                        );
                    }
                    $numbers[] = $item->getNativeValue();
                }

                if ([] === $numbers) {
                    throw new EvaluationException('median() requires a non-empty list', $call->getSpan());
                }

                return new FloatValue(Math\median($numbers));
            };
    }
}
