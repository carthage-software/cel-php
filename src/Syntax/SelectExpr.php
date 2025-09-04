<?php

declare(strict_types=1);

namespace Cel\Syntax;

use Override;

/**
 * Represents a field selection expression.
 * e.g., `message.field`
 */
final readonly class SelectExpr extends AbstractExpr
{
    /**
     * @param IdedExpr $operand The expression being selected from.
     * @param string   $field   The name of the field being selected.
     * @param bool     $isTest  Whether this is a test-only select.
     *                        e.g., `has(message.field)`
     */
    public function __construct(
        public IdedExpr $operand,
        public string $field,
        public bool $isTest,
    ) {}

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'select_expr' => [
                'operand' => $this->operand->jsonSerialize(),
                'field' => $this->field,
                'test_only' => $this->isTest,
            ],
        ];
    }
}
