<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Int;

use Cel\Exception\InternalException;
use Cel\Exception\OverflowException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\UnsignedIntegerValue;
use Cel\Value\Value;
use Override;
use Psl\Type;

use function bccomp;
use function sprintf;

use const PHP_INT_MAX;

/**
 * Handles int(unsigned_integer) -> integer
 */
final readonly class FromUnsignedIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws OverflowException If the unsigned integer value exceeds the maximum integer value.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, UnsignedIntegerValue::class);
        $uintValue = $value->value;

        if (Type\int()->matches($uintValue)) {
            return new IntegerValue($uintValue);
        }

        if (bccomp($uintValue, (string) PHP_INT_MAX) === 1) {
            throw new OverflowException(
                sprintf('Unsigned integer value %s overflows maximum integer value %d', $uintValue, PHP_INT_MAX),
                $call->getSpan(),
            );
        }

        return new IntegerValue((int) $uintValue);
    }
}
