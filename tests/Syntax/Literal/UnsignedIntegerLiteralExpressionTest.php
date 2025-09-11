<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @extends AbstractLiteralExpressionTestCase<int>
 */
#[CoversClass(UnsignedIntegerLiteralExpression::class)]
#[UsesClass(Span::class)]
final class UnsignedIntegerLiteralExpressionTest extends AbstractLiteralExpressionTestCase
{
    /**
     * @param int $value
     */
    #[Override]
    protected function createLiteral(mixed $value, string $raw, Span $span): array
    {
        $literal = new UnsignedIntegerLiteralExpression($value, $raw, $span);

        return [$literal, $value, $raw, $span, ExpressionKind::UIntLiteral];
    }

    #[Override]
    protected function getTestValue(): array
    {
        return [123, '123u'];
    }
}
