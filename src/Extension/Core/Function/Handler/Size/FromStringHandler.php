<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Size;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Str;

/**
 * Handles size(string) -> integer
 */
final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);

        return new IntegerValue(Str\length($value->value));
    }
}
