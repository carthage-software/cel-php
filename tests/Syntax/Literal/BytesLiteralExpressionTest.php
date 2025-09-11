<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\BytesLiteralExpression;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @extends AbstractLiteralExpressionTestCase<string>
 */
#[CoversClass(BytesLiteralExpression::class)]
#[UsesClass(Span::class)]
final class BytesLiteralExpressionTest extends AbstractLiteralExpressionTestCase
{
    /**
     * @param string $value
     */
    #[Override]
    protected function createLiteral(mixed $value, string $raw, Span $span): array
    {
        $literal = new BytesLiteralExpression($value, $raw, $span);

        return [$literal, $value, $raw, $span, ExpressionKind::BytesLiteral];
    }

    #[Override]
    protected function getTestValue(): array
    {
        return ['bytes_value', 'b"bytes_value"'];
    }
}
