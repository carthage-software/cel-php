<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\StringLiteralExpression;
use Override;

/**
 * @extends AbstractLiteralExpressionTestCase<string>
 */
final class StringLiteralExpressionTest extends AbstractLiteralExpressionTestCase
{
    /**
     * @param string $value
     */
    #[Override]
    protected function createLiteral(mixed $value, string $raw, Span $span): array
    {
        $literal = new StringLiteralExpression($value, $raw, $span);

        return [$literal, $value, $raw, $span, ExpressionKind::StringLiteral];
    }

    #[Override]
    protected function getTestValue(): array
    {
        return ['hello world', '"hello world"'];
    }
}
