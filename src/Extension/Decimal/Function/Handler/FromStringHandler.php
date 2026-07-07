<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal\Function\Handler;

use Cel\Exception\InternalException;
use Cel\Extension\Decimal\Message\DecimalNumber;
use Cel\Extension\Decimal\Util\DecimalFactory;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Throwable;

use function sprintf;

/**
 * Handles decimal(string) -> DecimalNumber
 *
 * @internal
 */
final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
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
        $arg = ArgumentsUtil::get($arguments, 0, StringValue::class);

        try {
            $decimal = DecimalFactory::from($arg->value);
        } catch (Throwable $e) {
            throw InternalException::forMessage(
                sprintf('Failed to create decimal from string "%s": %s', $arg->value, $e->getMessage()),
                $e,
            );
        }

        return new DecimalNumber($decimal)->toCelValue();
    }
}
