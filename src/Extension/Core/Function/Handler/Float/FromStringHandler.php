<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\Float;

use Cel\Exception\InternalException;
use Cel\Exception\TypeConversionException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Util\FloatParser;
use Cel\Value\FloatValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;

use function sprintf;

/**
 * Handles float(string) -> float
 */
final readonly class FromStringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws TypeConversionException If the string cannot be converted to a float.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, StringValue::class);

        $float = FloatParser::tryParse($value->value);
        if (null === $float) {
            throw new TypeConversionException(
                sprintf('Cannot convert string "%s" to float.', $value->value),
                $call->getSpan(),
            );
        }

        return new FloatValue($float);
    }
}
