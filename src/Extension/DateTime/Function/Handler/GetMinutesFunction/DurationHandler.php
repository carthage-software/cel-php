<?php

declare(strict_types=1);

namespace Cel\Extension\DateTime\Function\Handler\GetMinutesFunction;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\DurationValue;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;

final readonly class DurationHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $duration = ArgumentsUtil::get($arguments, 0, DurationValue::class);

        return new IntegerValue((int) $duration->value->getTotalMinutes());
    }
}
