<?php

declare(strict_types=1);

namespace Cel\Extension\Core;

use Cel\Extension\ExtensionInterface;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Override;

final readonly class CoreExtension implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new Function\IntFunction(),
            new Function\StringFunction(),
            new Function\UIntFunction(),
            new Function\FloatFunction(),
            new Function\BoolFunction(),
            new Function\SizeFunction(),
            new Function\BytesFunction(),
            new Function\TypeOfFunction(),
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
            new BinaryOperator\InOperator(),
            new BinaryOperator\LogicalAndOperator(),
            new BinaryOperator\LogicalOrOperator(),
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
            new UnaryOperator\LogicalNotOperator(),
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getMessageTypes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getValueResolvers(): array
    {
        return [];
    }
}
