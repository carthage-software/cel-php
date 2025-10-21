<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\EndsWith;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\BytesValue;
use Cel\Value\Value;
use Override;
use Psl\Exception\InvariantViolationException;
use Psl\Str;
use Psl\Str\Byte;

final readonly class BytesBytesHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return BooleanValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): BooleanValue
    {
        $target = ArgumentsUtil::get($arguments, 0, BytesValue::class);
        $suffix = ArgumentsUtil::get($arguments, 1, BytesValue::class);

        if ('' === $suffix->value) {
            return new BooleanValue(true);
        }

        try {
            return new BooleanValue(Byte\ends_with($target->value, $suffix->value));
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(
                Str\format('String operation failed: %s', $e->getMessage()),
                $call->getSpan(),
                $e,
            );
        }
    }
}
