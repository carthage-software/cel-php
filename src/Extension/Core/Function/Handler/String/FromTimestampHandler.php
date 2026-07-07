<?php

declare(strict_types=1);

namespace Cel\Extension\Core\Function\Handler\String;

use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\StringValue;
use Cel\Value\TimestampValue;
use Cel\Value\Value;
use Override;
use Psl\DateTime\DateTime;
use Psl\DateTime\Timezone;

use function rtrim;
use function sprintf;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Handles string(timestamp) -> string
 *
 * @internal
 */
final readonly class FromTimestampHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return Value The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): Value
    {
        $value = ArgumentsUtil::get($arguments, 0, TimestampValue::class);
        $datetime = DateTime::fromTimestamp($value->value, Timezone::UTC);

        $formatted = sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d%sZ',
            $datetime->getYear(),
            $datetime->getMonth(),
            $datetime->getDay(),
            $datetime->getHours(),
            $datetime->getMinutes(),
            $datetime->getSeconds(),
            self::fraction($datetime->getNanoseconds()),
        );

        return new StringValue($formatted);
    }

    private static function fraction(int $nanoseconds): string
    {
        if (0 === $nanoseconds) {
            return '';
        }

        return '.' . rtrim(str_pad((string) $nanoseconds, 9, '0', STR_PAD_LEFT), '0');
    }
}
