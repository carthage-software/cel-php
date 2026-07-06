<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\Matches;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Syntax\Member\CallExpression;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BooleanValue;
use Cel\Value\StringValue;
use Cel\Value\Value;
use Override;
use Psl\Regex;
use Psl\Regex\Exception\InvalidPatternException;

use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Handles matches(string, string) -> bool.
 */
final readonly class StringStringHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param CallExpression $call The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return BooleanValue The resulting value.
     *
     * @throws InternalException If argument type assertion fails.
     * @throws EvaluationException If the regular expression is invalid.
     */
    #[Override]
    public function __invoke(CallExpression $call, array $arguments): BooleanValue
    {
        $target = ArgumentsUtil::get($arguments, 0, StringValue::class);
        $pattern = ArgumentsUtil::get($arguments, 1, StringValue::class);

        try {
            return new BooleanValue(Regex\matches($target->value, self::compile($pattern->value)));
        } catch (InvalidPatternException $exception) {
            throw new EvaluationException(
                sprintf('Invalid regular expression `%s`: %s', $pattern->value, $exception->getMessage()),
                $call->getSpan(),
                $exception,
            );
        }
    }

    /**
     * @return non-empty-string
     */
    private static function compile(string $regex): string
    {
        foreach (['/', '#', '~', '%', '@', '!', ';', ','] as $delimiter) {
            if (!str_contains($regex, $delimiter)) {
                return $delimiter . $regex . $delimiter . 'u';
            }
        }

        return '#' . str_replace('#', '\\#', $regex) . '#u';
    }
}
