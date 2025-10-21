<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Override;

/**
 * @extends AbstractLiteralExpressionTestCase<int>
 */
final class IntegerLiteralExpressionTest extends AbstractLiteralExpressionTestCase
{
    /**
     * @param int $value
     */
    #[Override]
    protected function createLiteral(mixed $value, string $raw, Span $span): array
    {
        $literal = new IntegerLiteralExpression($value, $raw, $span);

        return [$literal, $value, $raw, $span, ExpressionKind::IntLiteral];
    }

    #[Override]
    protected function getTestValue(): array
    {
        return [123, '123'];
    }
}
