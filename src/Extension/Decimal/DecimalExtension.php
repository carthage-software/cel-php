<?php

declare(strict_types=1);

namespace Cel\Extension\Decimal;

use Cel\Extension\ExtensionInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Override;

/**
 * Extension providing arbitrary precision arithmetic via Decimal\Decimal.
 *
 * This extension:
 * - Provides a decimal() function to create Decimal numbers
 * - Provides a value resolver to convert Decimal\Decimal to DecimalNumber
 * - Provides operator overloads for all mathematical operations
 * - Supports operations between DecimalNumber and int/uint/float types
 * - Registers DecimalNumber as an available message type
 */
final readonly class DecimalExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\DecimalFunction(),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getBinaryOperatorOverloads(): array
    {
        return [
            new BinaryOperator\AdditionOperator(),
            new BinaryOperator\SubtractionOperator(),
            new BinaryOperator\MultiplicationOperator(),
            new BinaryOperator\DivisionOperator(),
            new BinaryOperator\ModuloOperator(),
            new BinaryOperator\ComparisonOperator(BinaryOperatorKind::LessThan),
            new BinaryOperator\ComparisonOperator(BinaryOperatorKind::LessThanOrEqual),
            new BinaryOperator\ComparisonOperator(BinaryOperatorKind::GreaterThan),
            new BinaryOperator\ComparisonOperator(BinaryOperatorKind::GreaterThanOrEqual),
            new BinaryOperator\EqualityOperator(BinaryOperatorKind::Equal),
            new BinaryOperator\EqualityOperator(BinaryOperatorKind::NotEqual),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getUnaryOperatorOverloads(): array
    {
        return [
            new UnaryOperator\NegationOperator(),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMessageTypes(): array
    {
        return [
            DecimalNumber::class => ['DecimalNumber', 'decimal'],
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValueResolvers(): array
    {
        return [
            new DecimalValueResolver(),
        ];
    }
}
