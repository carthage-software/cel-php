<?php

declare(strict_types=1);

namespace Cel\Extension\String\Function\Handler\ToAsciiLower;

use Cel\Exception\EvaluationException;
use Cel\Exception\InternalException;
use Cel\Function\FunctionOverloadHandlerInterface;
use Cel\Span\Span;
use Cel\Util\ArgumentsUtil;
use Cel\Value\BytesValue;
use Cel\Value\Value;
use Override;
use Psl\Exception\InvariantViolationException;
use Psl\Str;
use Psl\Str\Byte;

final readonly class BytesHandler implements FunctionOverloadHandlerInterface
{
    /**
     * @param Span $span The call expression.
     * @param list<Value> $arguments The function arguments.
     *
     * @return BytesValue The resulting value.
     *
     * @throws InternalException If an internal error occurs.
     * @throws EvaluationException If the string operation fails.
     */
    #[Override]
    public function __invoke(Span $span, array $arguments): BytesValue
    {
        $target = ArgumentsUtil::get($arguments, 0, BytesValue::class);

        try {
            $result = '';
            for ($i = 0; $i < Byte\length($target->value); ++$i) {
                $byte = $target->value[$i];
                $ord = Byte\ord($byte);
                // A = 65, Z = 90
                $result .= $ord >= 65 && $ord <= 90 ? Byte\chr($ord + 32) : $byte;
            }

            return new BytesValue($result);
        } catch (InvariantViolationException $e) {
            throw new EvaluationException(Str\format('String operation failed: %s', $e->getMessage()), $span, $e);
        }
    }
}
