<?php

declare(strict_types=1);

namespace Cel\Runtime\Extension\DateTime\Function;

use Cel\Runtime\Function\FunctionInterface;
use Cel\Runtime\Value\TimestampValue;
use Cel\Runtime\Value\Value;
use Cel\Syntax\Member\CallExpression;
use Override;
use Psl\DateTime\Timestamp;

/**
 * @mago-expect analysis:unused-parameter
 */
final readonly class NowFunction implements FunctionInterface
{
    #[Override]
    public function getName(): string
    {
        return 'now';
    }

    #[Override]
    public function isIdempotent(): bool
    {
        return false;
    }

    #[Override]
    public function getOverloads(): iterable
    {
        yield [] =>
            /**
             * @param CallExpression $call      The call expression representing the function call.
             * @param list<Value>    $arguments The arguments passed to the function.
             */
            static fn(CallExpression $call, array $arguments): TimestampValue => new TimestampValue(Timestamp::now());
    }
}
