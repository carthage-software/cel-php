<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Override;

/**
 * @extends AbstractLiteralExpressionTestCase<float>
 */
final class FloatLiteralExpressionTest extends AbstractLiteralExpressionTestCase
{
    /**
     * @param float $value
     */
    #[Override]
    protected function createLiteral(mixed $value, string $raw, Span $span): array
    {
        $literal = new FloatLiteralExpression($value, $raw, $span);

        return [$literal, $value, $raw, $span, ExpressionKind::FloatLiteral];
    }

    #[Override]
    protected function getTestValue(): array
    {
        return [123.45, '123.45'];
    }
}
