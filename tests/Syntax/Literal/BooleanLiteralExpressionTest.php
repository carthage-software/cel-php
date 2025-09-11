<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @extends AbstractLiteralExpressionTestCase<bool>
 */
#[CoversClass(BoolLiteralExpression::class)]
#[UsesClass(Span::class)]
final class BooleanLiteralExpressionTest extends AbstractLiteralExpressionTestCase
{
    /**
     * @param bool $value
     */
    #[Override]
    protected function createLiteral(mixed $value, string $raw, Span $span): array
    {
        $literal = new BoolLiteralExpression($value, $raw, $span);

        return [$literal, $value, $raw, $span, ExpressionKind::BoolLiteral];
    }

    #[Override]
    protected function getTestValue(): array
    {
        return [true, 'true'];
    }
}
