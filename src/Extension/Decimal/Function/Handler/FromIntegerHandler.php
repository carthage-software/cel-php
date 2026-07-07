<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\Message\DecimalNumber;
use Cel\Extension\Decimal\Util\DecimalFactory;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\IntegerValue;
use Cel\Value\Value;
use Override;
use Throwable;

use function sprintf;

/**
 * Handles decimal(int) -> DecimalNumber
 *
 * @internal
 */
final readonly class FromIntegerHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting DecimalNumber value.
     *
     * @throws InternalException If the Decimal creation fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $arg = ArgumentsUtil::get($arguments, 0, IntegerValue::class);

        try {
            $decimal = DecimalFactory::from((string) $arg->value);
        } catch (Throwable $e) {
            throw InternalException::forMessage(
                sprintf('Failed to create decimal from integer: %s', $e->getMessage()),
                $e,
            );
        }

        return new DecimalNumber($decimal)->toCelValue();
    }
}
