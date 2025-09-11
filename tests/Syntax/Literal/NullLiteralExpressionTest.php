<?php

declare(strict_types=1);

namespace Cel\Tests\Syntax\Literal;

use Cel\Span\Span;
use Cel\Syntax\ExpressionKind;
use Cel\Syntax\Literal\NullLiteralExpression;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @extends AbstractLiteralExpressionTestCase<null>
 */
#[CoversClass(NullLiteralExpression::class)]
#[UsesClass(Span::class)]
final class NullLiteralExpressionTest extends AbstractLiteralExpressionTestCase
{
    /**
     * @param null $value
     */
    #[Override]
    protected function createLiteral(mixed $value, string $raw, Span $span): array
    {
        $literal = new NullLiteralExpression($raw, $span);

        return [$literal, $value, $raw, $span, ExpressionKind::NullLiteral];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getTestValue(): array
    {
        return [null, 'null'];
    }
}
